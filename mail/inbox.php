<?php
require_once("../include/header.php");
gethead(1,"","邮件");

$uid = $_SESSION[ID];

$p = new DataAccess();


$sql = "select * from mail where mid = {$_GET['mid']}";
$p->dosql($sql);
$d=$p->rtnrlt();

$sql = "update mail set readed = 1 where mid = {$_GET['mid']} and toid = $uid";
$p->dosql($sql);
?>

<center>
<table width=60% border=2>
<tr>
<th>邮件主题</th>
<td width="80%"><?=$d['title']?></td>
</tr>
<tr>
<th>发件人ID</th>
<td><?=$d['fromid']?></td>
</tr>
<tr>
<th>收件人ID</th>
<td><?=$d['toid']?></td>
</tr>
<tr>
<th>发送时间</th>
<td><?=date('Y-m-d H:i:s',$d['time'])?></td>
</tr>
<tr>
<th>邮件内容</th>
<td><div class="MainText" width=400px><?=nl2br(sp2n(htmlspecialchars($d['msg'])))?></div></td>
</tr>
</table>

<form id="form" name="form" method="post" action="index.php">
<input name="fromid" type="hidden" value=<?=$uid?> />
<input name="toid" type="hidden" value=<?=$d['fromid']?> />
<input name="title" type="hidden" value="回(<?=$d['mid']?>)：<?=$d['title']?>" />
<input type="submit" name="Submit" value="回复该邮件" class="LinkButton"/>
</form>

</center>


<?
include_once("../include/footer.php");
?>

