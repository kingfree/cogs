<?php
require_once("../include/header.php");
gethead(1,"sess","邮件列表");

$uid = $_SESSION['ID'];

$p = new DataAccess();
$q = new DataAccess();
?>
<div class='center'>
<a class='btn btn-success' href="#sendmail" data-toggle='modal'><i class='icon-envelope icon-white'></i>发送信件</a>
</div>
<div class='container-fluid'>
<div id='sendmail' class='modal <?if(!isset($_POST['title'])) echo "hide"?> fade in'>
<form method="post" action="send.php" class='form-horizontal'>
<fieldset>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h2>发送信件</h2>
</div>
<div class='modal-body'>
<input name="fromid" type="hidden" value="<?=$uid?>" />
<div class='control-group'>
<label class='control-label' for='title'>邮件主题</label>
<div class='controls'><input id='title' name="title" value="<?=$_POST['title']?>"/></div>
</div>
<div class='control-group'>
<label class='control-label' for='toid'>收件人ID</label>
<div class='controls'><input id='toid' name="toid" value="<?=$_POST['toid']?$_POST['toid']:$_GET['toid']?>"/></div>
</div>
<div class='control-group'>
<label class='control-label' for='msg'>邮件内容</label>
<div class='controls'><textarea id='msg' name="msg" class='textarea' ><?=$_POST['text']?></textarea></div>
</div>
</div>
<div class='modal-footer'>
<button data-dismiss='modal' class='btn'>取消</button>
<button type="submit" class='btn btn-primary'>发送站内邮件</button>
</div>
</fieldset>
</form>
</div>
<div class='span6'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th>ID</th>
<th>收件人</th>
<th>标题</th>
</tr>
<?
$sql = "select mail.*,userinfo.* from mail,userinfo where mail.fromid = $uid and mail.toid = userinfo.uid order by mid desc";
$cnt = $p->dosql($sql);
for($i=0; $i<$cnt; $i++) {
	$d=$p->rtnrlt($i);
	echo "<tr>";
	echo "<td width=30px>{$d['mid']}</td> <td width=80px>";
	if($d['readed'] ==  0) echo "❤";
	echo "<a href='../user/detail.php?uid={$d['toid']}' target='_blank'>".gravatar::showImage($d['email'])."{$d['nickname']}</a></td>";
	$url = "inbox.php?mid={$d['mid']}";
	echo "<td><a href='$url' target='_blank'>".$d['title']."</a></td>";
	echo "</tr>";
}
?>
</table>
</div>
<div class='span6'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th>ID</th>
<th>发件人</th>
<th>标题</th>
<th width=80px>发送时间</th>
</tr>
<?
$sql = "select mail.*,userinfo.* from mail,userinfo where mail.toid = $uid and mail.fromid = userinfo.uid order by mid desc";
$cnt = $p->dosql($sql);
for($i=0; $i<$cnt; $i++) {
	$d=$p->rtnrlt($i);
	echo "<tr>";
	echo "<td width=30px>{$d['mid']}</td> <td width=80px>";
	echo "<a href='../user/detail.php?uid={$d['toid']}' target='_blank'>".gravatar::showImage($d['email'])."{$d['nickname']}</a></td>";
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
</div>
</div>
<?
include_once("../include/footer.php");
?>
