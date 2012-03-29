<?php
require_once("../include/stdhead.php");
gethead(1,"","论坛系统");
$p = new DataAccess();
$q = new DataAccess();
$sql = "select discuss.*,problem.probname,userinfo.nickname,userinfo.email from discuss,problem,userinfo where fid=0 and discuss.pid=problem.pid and userinfo.uid=discuss.uid order by did desc";
$cnt = $p->dosql($sql);
$page = (int) $_GET['page'];
$st = $page ? (($page-1)*$SETTINGS['style_pagesize']) : 0;
?>
<center>

<table id='forum' width=100%>
<tr>
<th>编号</th>
<th>题目</th>
<th>标题</th>
<th>发表</th>
<th>回复</th>
<th>最后回复时间</th>
<th>权值</th>
</tr>
<?
for ($i=$st;$i<$cnt && $i<$st+$SETTINGS['style_pagesize'];$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td><?=$d['did']?></td>
<td><a href="../problem/pdetail.php?pid=<?=$d['pid']?>"><?=$d['probname']?></a></td>
<td><a href="page.php?did=<?=$d['did']?>"><?=$d['title']?></a></td>
<td><a href="../user/detail.php?uid=<?=$d['uid']?>">
<?=gravatar::showImage($d['email'], 16);?><?=$d['nickname']?></a></td>
<?
$sql1 = "select discuss.*,userinfo.nickname,userinfo.email from discuss,problem,userinfo where (fid={$d['did']}) and discuss.pid=problem.pid and userinfo.uid=discuss.uid order by did desc limit 1";
$cnt1 = $q->dosql($sql1);
if($cnt1) {
$e=$q->rtnrlt(0);
?>
<td><a href="../user/detail.php?uid=<?=$e['uid']?>">
<?=gravatar::showImage($e['email'], 16);?><?=$e['nickname']?></a></td>
<td><?=date('Y-m-d H:i',$e['time']) ?></td>
<?
} else {
?>
<td>无</td>
<td><?=date('Y-m-d H:i',$d['time']) ?></td>
<? } ?>
<td><?=($d['up']-$d['down'])?></td>
</tr>
<?
}
?>
</table>
</center>

<?
page_slice($cnt,$page);
require_once("../include/stdtail.php");
?>
