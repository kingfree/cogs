<?php
require_once("./include/stdhead.php");
gethead(1,"","首页");
$p=new DataAccess();
$q=new DataAccess();
?>
<table id="index">
<tr>
<td id="index_prob">
<table id="prob_short">
<tr><form id="search" name="search" method="get" action="problem/problist.php">
<td><input name="key" type="text" id="key" /></td>
<td><input type="submit" value="搜索"/></td>
</form></tr>
<tr><form id="gotoprob" name="gotoprob" method="get" action="problem/pdetail.php">
<td><input name="pid" type="text" id="pid" value=1 /></td>
<td><input type="submit" value="进入" /></td>
</form></tr>
<tr><th colspan=2>
<a href="problem/random.php" title="随机选择一道你没有通过的题目">随机题目</a>
</th></tr>
</table>
<br />
<?php 
$now = time();
$cnt = $p->dosql("select comptime.*,compbase.*,userinfo.nickname,groups.* from comptime,compbase,userinfo,groups where comptime.cbid=compbase.cbid and userinfo.uid=compbase.ouid and comptime.group=groups.gid and endtime > $now order by starttime asc");
for($i=0; $i<$cnt; $i++) {
    $d=$p->rtnrlt($i);
?>
<table class="index_contest">
  <tr>
    <th width="40px">比赛</th>
    <th><?php echo $d[cname] ?></th>
  </tr>
  <tr>
    <td>状态</td>
    <td><?php
	 if (time()>$d[endtime]) echo "<span class='did'>已结束</span>"; else
	 if (time()<$d[endtime] && time()>$d[starttime]) echo "<a href='competition/comp.php?ctid={$d[ctid]}&uid={$_SESSION['ID']}'><span class='doing'>正在进行...</span></a>"; else
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
    <td><a href="../information/userlist.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
  </tr>
  <tr>
    <td>介绍</td>
    <td><?php echo nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
  </tr>
</table>
<?php } ?>
<br />
<table id="lastest_prob">
<tr>
<th width="40px">PID</th>
<th>最新题目</th>
</tr>
<?php 
$cnt=$p->dosql("select * from problem where submitable=1 order by pid desc limit 5");
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
?>
<tr>
<td><?php echo $d['pid']; ?></td>
<td>
<? 是否通过($d['pid'], $q); ?>
<a href="problem/pdetail.php?pid=<?php echo $d['pid']; ?>" target="_blank"><?php echo $d['probname']; ?></a></td>
</tr>
<?php } ?>
</table>
</td>
<td id="index_text">
<marquee id="publicbar" id="publicbar" align="right" direction="left" scrollamount="5" onMouseOver="this.stop();" onMouseOut="this.start();">
<?php if ($_SESSION['admin']==2) { ?>[<a href="<?=pathconvert($SET['cur'],$editbulletin);?>">修改</a>]<?php } ?><font color="#003366"><b>公告 &gt;&gt;</b></font>
<?=输出文本($SET['global_bulletin']); ?>
</marquee>
<?php echo 输出文本($SET['global_index']); ?>
</td>
<td id="index_rank">
<table>
<tr><th colspan=5>等级前 <?=$SET['style_ranksize'];?> 名</th></tr>
<tr><th></th><th></th><th>用户</th><th>等级</th><th>过</th></tr>
<?php 
$cnt=$p->dosql("select * from userinfo order by grade desc limit 0, {$SET['style_ranksize']}");
for($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td><i><?=$i+1 ?></i></td>
<td style="font-size:26px; padding:0; margin:0;"><a href="user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?=gravatar::showImage($d['email'], 28);?></a></td>
<td><a href="user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?=$d['nickname']?></a></td>
<td><b><?php echo $d['grade'] ?></b></td>
<td><b><?php echo $d['accepted'] ?></b></td>
</tr>
<?php } ?>
</table>
</td>
</table>
<?php
require_once("./include/stdtail.php");
?>
