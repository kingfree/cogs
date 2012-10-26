<?php
require_once("./include/header.php");
gethead(1,"","首页");
$p=new DataAccess();
$q=new DataAccess();
$sizee=$SET['style_ranksize'] - 7;
?>
<div class='row-fluid'>
<div class='span9'>
<div class='row-fluid'>
<div class='span4'>
<?php 
$now = time();
$noww = time() + 60*60*2;
$cnt = $p->dosql("select comptime.*,compbase.*,userinfo.nickname,groups.* from comptime,compbase,userinfo,groups where comptime.cbid=compbase.cbid and userinfo.uid=compbase.ouid and comptime.group=groups.gid and endtime > $now and starttime < $noww order by starttime asc");
if($cnt) {
    for($i=0; $i<$cnt; $i++) {
        $d=$p->rtnrlt($i);
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th style="width: 3em;">比赛</th>
<td><b><?php echo $d[cname] ?></b></td>
</tr>
<tr>
<th>状态</th>
<td><?php
if (time()>$d[endtime]) echo "<span class='did'>已结束</span>"; else
if (time()<$d[endtime] && time()>$d[starttime]) echo "<a href='contest/problem.php?ctid={$d[ctid]}'><span class='doing'>正在进行...</span></a>"; else
echo "<span class='todo'>还未开始</span>"; 
?></td>
</tr>
<tr>
<th>开始</th>
<td><?php echo date('Y-m-d H:i:s', $d[starttime]) ?></td>
</tr>
<tr>
<th>结束</th>
<td><?php echo date('Y-m-d H:i:s', $d[endtime]) ?></td>
</tr>
<tr>
<th>分组</th>
<td><a href="../user/index.php?gid=<?php echo $d['gid'] ?>"><?php echo $d['gname'] ?></a></td>
</tr>
<tr>
<th>介绍</th>
<td><?php echo nl2br(BBCode(sp2n(htmlspecialchars($d[intro])))) ?></td>
</tr>
</table>
<?php } ?>
<br />
<? } else { ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php 
$cnt=$p->dosql("select * from page order by etime desc limit 6");
for($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr><td><a href="page/page.php?aid=<?=$d['aid']?>" title="<?=$d['title']?>"><?=shortname($d['title'])?></a></td></td>
<?php } ?>
</table>
<? } ?>
</div>
<div class='span8'>
<div class='alert alert-info'>
<?=输出文本($SET['global_bulletin']); ?>
</div>
<div id='index'>
<?php //echo 输出文本($SET['global_index']); ?>
<?php echo 输出文本($SET['global_head']); ?>
</div>
</div>
</div>
<div class='row-fluid'>
<div class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead>
<tr>
<th style="width: 4ex;">PID</th>
<th>最新题目
<a href="problem/random.php" title="随机选择一道你没有通过的题目" class='btn btn-danger btn-mini pull-right' >随机题目</a>
</th>
</thead>
</tr>
<?php 
$cnt=$p->dosql("select * from problem where submitable=1 order by addtime desc limit {$SET['index_problem_size']}");
for($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td><?php echo $d['pid']; ?></td>
<td>
<? 是否通过($d['pid'], $q); ?>
<a href="problem/problem.php?pid=<?php echo $d['pid']; ?>" title="<?=$d['probname']?>"><?php echo shortname($d['probname']); ?></a></td>
</tr>
<?php } ?>
</table>
</div>
<div class='span8'>
<table class='table table-striped table-condensed table-bordered fixed'>
<thead><tr>
<th>题目</th>
<th>用户</th>
<th style="width: 12ex;">评测结果</th>
<th style="width: 5ex;">得分</th>
<th style="width: 10ex;">提交时间</th>
</tr></thead>
<?php 
$cnt=$p->dosql("select submit.sid,submit.pid,submit.uid,submit.result,submit.score,submit.accepted,submit.subtime,problem.probname,userinfo.nickname,userinfo.realname,userinfo.email,userinfo.memo from submit,problem,userinfo where submit.score>={$SET['index_submit_score']} and submit.uid=userinfo.uid and submit.pid=problem.pid order by submit.sid desc limit {$SET['index_submit_size']}");
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
?>
<tr>
<td><?php if(!$_GET['pid']) {
    是否通过($d['pid'], $q);
    echo "<a href='submit/?pid={$d['pid']}'>".shortname($d['probname'])."</a>";
    echo "<a href='problem/problem.php?pid={$d['pid']}' title='{$d['probname']}' target='_blank'><span class='icon-share'></span></a>";
} else
    echo "<a href='problem/problem.php?pid={$d['pid']}' title='{$d['probname']}' target='_blank'>".shortname($d['probname'])."</a>";
?></td>
<td><a href='user/detail.php?uid=<?=$d['uid']?>' title="<?=(sp2n(htmlspecialchars($d['memo'])))?>" target='_blank'><?=gravatar::showImage($d['email']);?></a>
<?php echo "<a href='submit/?uid={$d[uid]}'>";
if(有此权限("查看用户")) echo $d['realname']; else echo $d['nickname'];
echo "</a>"; ?></td>
<td><a href='submit/code.php?id=<?=$d['sid']?>' title="<?=$d['result']?>"><?=评测结果($d['result'], 10, true)?></a></td>
<td><span class="<?=$d['accepted']?'ok':'no'?>"><?=$d['score'] ?></span></td>
<td><?php echo date('H:i:s',$d['subtime']); ?></td>
</tr>
<?php } ?>
</table>
</div>
</div>
</div>
<div class='span3'>
<table class='table table-striped table-condensed table-bordered fixed'>
<thead>
<tr>
<th style="width: 2ex;"></th>
<th>用户排名</th>
<th style="width: 5ex;">积分</th>
<th style="width: 4ex;">题目</th>
</tr>
</thead>
<?php 
$cnt=$p->dosql("select * from userinfo order by grade desc limit 0, {$SET['style_ranksize']}");
for($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td><i><?=$i+1 ?></i></td>
<td><a href="user/detail.php?uid=<?php echo $d['uid']; ?>" title="<?=(sp2n(htmlspecialchars($d['memo'])))?>"><?=gravatar::showImage($d['email'], $SET['index_rank_icon']);?><?php if(有此权限("查看用户")) echo $d['realname']; else echo $d['nickname'];?></a></td>
<td><?=$d['grade']?></td>
<td><?=$d['accepted']?></td>
</tr>
<?php } ?>
</table>
</div>
</div>
<?php
require_once("./include/footer.php");
?>
