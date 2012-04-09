<?php
require_once("../include/stdhead.php");
gethead(1,"sess","站内邮件");

$uid = $_SESSION[ID];

$p = new DataAccess();
$q = new DataAccess();
?>

<table width=100% border=2>
<tr>
<th width="70px">写邮件</th>
<th>收件箱</th>
<th>发件箱</th>
</tr>
<tr>
<td valign=top>
<form id="form" name="form" method="post" action="send.php">
<input name="fromid" type="hidden" value=<?=$uid?> />
<table>
<tr>
<th>邮件主题</th>
<td><input class="InputBox" name="title" value="<?=$_POST['title']?>" /></td>
</tr>
<tr>
<th>收件人ID</th>
<td><input class="InputBox" name="toid" value="<?=$_POST['toid']?>" /></td>
</tr>
<tr>
<th>邮件内容</th>
<td><textarea class="InputBox" name="msg" cols="30" rows="10"><?=$_POST['text']?></textarea></td>
</tr>
<tr>
<th></th>
<td><input class="LinkButton" type="submit" name="Submit" value="发送站内邮件"  class="Button"/></td>
</tr>
</table>
</form>
</td>
<td width=30% valign=top>
<table width=100% border=1>
<tr>
<th>ID</th>
<th>发件人</th>
<th>标题</th>
<th width=80px>发送时间</th>
</tr>
<?
$sql = "select * from mail where toid = $uid order by mid desc limit 10";
$cnt = $p->dosql($sql);
for($i=0; $i<$cnt; $i++) {
	$d=$p->rtnrlt($i);
	$sql = "select nickname from userinfo where uid = {$d['fromid']}";
	$q->dosql($sql);
	$e = $q->rtnrlt();
	echo "<tr>";
	echo "<td>{$d['mid']}</td>";
	echo "<td><a href='../user/detail.php?uid={$d['fromid']}' target='_blank'>{$e['nickname']}</a></td>";
	$url = "inbox.php?mid={$d['mid']}";
	if($d['readed'] ==  0)
	echo "<td style='background-color:#FF0000;'><b><a href='$url' target='_blank'>".$d['title']."</a></b></td>";
	else
	echo "<td><a href='$url' target='_blank'>".$d['title']."</a></td>";
	echo "<td>".date('Y-m-d',$d['time'])."</td>";
	echo "</tr>";
}
?>
</table>
</td>
<td width=30% valign=top>
<table width=100% border=1>
<tr>
<th>ID</th>
<th>收件人</th>
<th>标题</th>
<th width=80px>发送时间</th>
</tr>
<?
$sql = "select * from mail where fromid = $uid order by mid desc limit 10";
$cnt = $p->dosql($sql);
for($i=0; $i<$cnt; $i++) {
	$d=$p->rtnrlt($i);
	$sql = "select nickname from userinfo where uid = {$d['toid']}";
	$q->dosql($sql);
	$e = $q->rtnrlt();
	echo "<tr>";
	echo "<td>{$d['mid']}</td> <td>";
	if($d['readed'] ==  0) echo "❤";
	echo "<a href='../user/detail.php?uid={$d['toid']}' target='_blank'>{$e['nickname']}</a></td>";
	$url = "inbox.php?mid={$d['mid']}";
	echo "<td><a href='$url' target='_blank'>".$d['title']."</a></td>";
	echo "<td>".date('Y-m-d',$d['time'])."</td>";
	echo "</tr>";
}
?>
</table>
</td>
</tr>
</table>

<?
include_once("../include/stdtail.php");
?>
