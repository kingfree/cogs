<?php
require_once("./include/header.php");
gethead(1,"","首页");
$p=new DataAccess();
$q=new DataAccess();
?>
<div class='container-fluid'>
<div class='span3'>
<?php 
$now = time();
$cnt = $p->dosql("select comptime.*,compbase.*,userinfo.nickname,groups.* from comptime,compbase,userinfo,groups where comptime.cbid=compbase.cbid and userinfo.uid=compbase.ouid and comptime.group=groups.gid and endtime > $now order by starttime asc");
if($cnt) {
for($i=0; $i<$cnt; $i++) {
$d=$p->rtnrlt($i);
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th width="40px">比赛</th>
<th><?php echo $d[cname] ?></th>
</tr>
<tr>
<td>状态</td>
<td><?php
if (time()>$d[endtime]) echo "<span class='did'>已结束</span>"; else
if (time()<$d[endtime] && time()>$d[starttime]) echo "<a href='contest/comp.php?ctid={$d[ctid]}&uid={$_SESSION['ID']}'><span class='doing'>正在进行...</span></a>"; else
echo "<span class='todo'>还未开始</span>"; 
?></td>
</tr>
<tr>
<td>开始</td>
<td><?php echo date('Y-m-d H:i:s', $d[starttime]) ?></td>
</tr>
<tr>
<td>结束</td>
<td><?php echo date('Y-m-d H:i:s', $d[endtime]) ?></td>
</tr>
<tr>
<td>分组</td>
<td><a href="../user/index.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
</tr>
<tr>
<td>介绍</td>
<td><?php echo nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
</tr>
</table>
<?php } ?>
<br />
<? } else { ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php 
$cnt=$p->dosql("select * from page order by etime desc limit 8");
for($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr><td><a href="page/page.php?aid=<?=$d['aid']?>" target="_blank"><?=$d['title']?></a></td></td>
<?php } ?>
</table>
<? } ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead>
<tr>
<th width="40px">PID</th>
<th>最新题目</th>
</thead>
</tr>
<?php 
$cnt=$p->dosql("select * from problem where submitable=1 order by pid desc limit 8");
for($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td><?php echo $d['pid']; ?></td>
<td>
<? 是否通过($d['pid'], $q); ?>
<a href="problem/problem.php?pid=<?php echo $d['pid']; ?>" target="_blank"><?php echo $d['probname']; ?></a></td>
</tr>
<?php } ?>
</table>
</div>
<div class='span6'>
<div class='alert alert-info'>
<?php if(有此权限('参数设置')) { ?>[<a href="<?=路径("admin/settings/editkey.php?sname=global_bulletin&method=html")?>">修改</a>]<?php } ?><font color="#003366"><b>公告 &gt;&gt;</b></font><br />
<?=输出文本($SET['global_bulletin']); ?>
</div>
<div style="margin-right:16px;">
<?php echo 输出文本($SET['global_index']); ?>
</div>
</div>
<div class='span3'>
<table class='table table-striped table-condensed table-bordered fixed'>
<thead>
<tr>
<td width='12px'></td>
<td width='28px'>头像</td>
<td>用户</td>
<td width='30px'>等级</td>
<td width='30px'>题目</td>
</tr>
</thead>
<?php 
$cnt=$p->dosql("select * from userinfo order by grade desc limit 0, {$SET['style_ranksize']}");
for($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td><i><?=$i+1 ?></i></td>
<td class='jin'><a href="user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?=gravatar::showImage($d['email'], 28);?></a></td>
<td><a href="user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?=$d['nickname']?></a></td>
<td ><b><?php echo $d['grade'] ?></b></td>
<td ><b><?php echo $d['accepted'] ?></b></td>
</tr>
<?php } ?>
</table>
</div>
</div>
<?php
require_once("./include/footer.php");
?>
