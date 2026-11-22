<?php
require_once("../include/header.php");
require_once(dirname(__FILE__)."/message.php");
gethead(1,"sess","邮件列表");

$uid = (int)$_SESSION['ID'];

$p = new DataAccess();
$q = new DataAccess();

if($_GET['toid']) $_POST['title'] = "给".$_GET['toid']."的邮件";

?>
<div class='center'>
<a class='btn btn-success' href="#sendmail" data-toggle='modal'><i class='icon-envelope icon-white'></i>发送信件</a>
</div>
<div id='sendmail' class='modal hide fade in'>
<form method="post" action="send.php" class='form-horizontal'>
<fieldset>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3>发送信件</h3>
</div>
<div class='modal-body'>
<div class='control-group'>
<label class='control-label' for='title'>邮件主题</label>
<div class='controls'><input type='text' id='title' name="title" value="<?=mailHtml($_POST['title'])?>"/></div>
</div>
<div class='control-group'>
<label class='control-label' for='toid'>收件人ID</label>
<div class='controls'><input type='number' id='toid' name="toid" value="<?=mailHtml($_POST['toid']?$_POST['toid']:$_GET['toid'])?>"/></div>
</div>
<div class='control-group'>
<label class='control-label' for='msg'>邮件内容</label>
<div class='controls'><textarea id='msg' name="msg" class='textarea' ><?=mailHtml($_POST['text'])?></textarea></div>
</div>
</div>
<div class='modal-footer'>
<button data-dismiss='modal' class='btn'>取消</button>
<button type="submit" class='btn btn-primary'>发送站内邮件</button>
</div>
</fieldset>
</form>
</div>

<script>
<?php if($_POST['title']) echo "$('#sendmail').modal('show');";?>
function markread(id) {
  $.get("markread.php",{mid: id},function(txt){});
}
function reply(uid, tit) {
  $('#toid').attr('value', uid);
  $('#title').attr('value', '回(' + tit + '): ');
  $('.modal').modal('hide');
  $('#sendmail').modal('show');
};
</script>

<div class='row-fluid'>
<div class='span6'>
<table class='table table-condensed table-bordered fiexd'>
<tr>
<td>ID</td>
<td>收件人</td>
<td>发件箱</td>
</tr>
<?php
$sql = new SqlQuery("select mail.*,userinfo.* from mail,userinfo where mail.fromid = ? and mail.toid = userinfo.uid order by mid desc", [$uid]);
$cnt = $p->dosql($sql);
for($i=0; $i<$cnt; $i++) {
	$d=$p->rtnrlt($i);
    $title = mailHtml($d['title']);
    $nickname = mailHtml($d['nickname']);
	if($d['readed'] ==  0) echo "<tr class='success'>"; else echo "<tr>";
	echo "<td>{$d['mid']}</td> <td>";
	echo "<a href='../user/detail.php?uid={$d['toid']}' target='_blank'>".gravatar::showImage($d['email'])."{$nickname}</a></td>";
    echo "<td><a href='#mail{$d['mid']}' data-toggle='modal'>{$title}</a></td>";
	echo "</tr>";
?>
<div id='mail<?=$d['mid']?>' class='modal hide fade in'>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3><?=mailHtml($d['title'])?></h3>
</div>
<div class='modal-body'>
<?=nl2br(mailHtml($d['msg']))?>
</div>
<div class='modal-footer'>
<span class='pull-left'>
<a href='../user/detail.php?uid=<?=$d['toid']?>' target='_blank'>
<?=gravatar::showImage($d['email'])?>
<?=$nickname?></a>
<?=date('Y-m-d H:i:s',$d['time'])?>
</span>
<button data-dismiss='modal' class='btn'>关闭</button>
</div>
</div>
<?php
}
?>
</table>
</div>
<div class='span6'>
<table class='table table-condensed table-bordered fiexd'>
<tr class='info'>
<td>ID</td>
<td>发件人</td>
<td><b>收件箱</b></td>
<td width=80px>收到时间</td>
</tr>
<?php
$sql = new SqlQuery("select mail.*,userinfo.* from mail,userinfo where mail.toid = ? and mail.fromid = userinfo.uid order by mid desc", [$uid]);
$cnt = $p->dosql($sql);
for($i=0; $i<$cnt; $i++) {
	$d=$p->rtnrlt($i);
    $title = mailHtml($d['title']);
    $nickname = mailHtml($d['nickname']);
	if($d['readed'] ==  0) echo "<tr class='error'>"; else echo "<tr>";
	echo "<td>{$d['mid']}</td> <td>";
	echo "<a href='../user/detail.php?uid={$d['fromid']}' target='_blank'>".gravatar::showImage($d['email'])."{$nickname}</a></td>";
    $url="<a href='#mail{$d['mid']}' data-toggle='modal' onclick='markread({$d['mid']})'>{$title}</a>";
	echo "<td>$url</td>";
	echo "<td>".date('Y-m-d',$d['time'])."</td>";
	echo "</tr>";
?>
<div id='mail<?=$d['mid']?>' class='modal hide fade in'>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3><?=mailHtml($d['title'])?></h3>
</div>
<div class='modal-body'>
<?=nl2br(mailHtml($d['msg']))?>
</div>
<div class='modal-footer'>
<span class='pull-left'>
<a href='../user/detail.php?uid=<?=$d['fromid']?>' target='_blank'>
<?=gravatar::showImage($d['email'])?>
<?=$nickname?></a>
<?=date('Y-m-d H:i:s',$d['time'])?>
</span>
<a class='btn btn-success' onclick="reply(<?=$d['fromid']?>, <?=$d['mid']?>)">回复</a>
<button data-dismiss='modal' class='btn'>关闭</button>
</div>
</div>
<?php
}
?>
</table>
</div>
</div>
<?php
include_once("../include/footer.php");
?>
