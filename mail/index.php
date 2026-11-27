<?php
// 访问 mail/index.php 查看本人的收件箱、发件箱并写信；mail/index.php?toid=1 会预填收件人。
require_once(__DIR__.'/../include/header.php');
gethead(1,"sess","邮件列表");

$uid = (int)$_SESSION['ID'];
$recipient = $_POST['toid'] ?? $_GET['toid'] ?? ''; // 回复和入组申请从表单带入收件人，个人主页通过链接带入。
$title = $_POST['title'] ?? ($recipient !== '' ? '给'.$recipient.'的邮件' : ''); // 回复和入组申请沿用预填主题；由收件人链接进入时生成默认主题。
$boxes = ['sent'=>Message::sent($uid), 'received'=>Message::received($uid)]; // 两栏使用当前会话 UID 分别读取，模板只负责显示联系人。
?>
<div class='center'>
<a class='btn btn-success' href="#sendmail" data-toggle='modal'><i class='icon-envelope icon-white'></i>发送信件</a>
</div>
<div id='sendmail' class='modal hide fade in'>
<form method="post" action="send.php" class='form-horizontal'><?=formTokenField()?>
<fieldset>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3>发送信件</h3>
</div>
<div class='modal-body'>
<div class='control-group'>
<label class='control-label' for='title'>邮件主题</label>
<div class='controls'><input type='text' id='title' name="title" value="<?=h($title)?>"/></div>
</div>
<div class='control-group'>
<label class='control-label' for='toid'>收件人ID</label>
<div class='controls'><input type='number' id='toid' name="toid" value="<?=h($recipient)?>"/></div>
</div>
<div class='control-group'>
<label class='control-label' for='msg'>邮件内容</label>
<div class='controls'><textarea id='msg' name="msg" class='textarea' ><?=h($_POST['text'] ?? '')?></textarea></div>
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
<?php if ($title !== '') echo "$('#sendmail').modal('show');";?>
function markread(id) {
  $.post("markread.php",{mid: id, form_token: <?=json_encode(formToken())?>});
}
function reply(uid, tit) {
  $('#toid').attr('value', uid);
  $('#title').attr('value', '回(' + tit + '): ');
  $('.modal').modal('hide');
  $('#sendmail').modal('show');
}
</script>

<div class="row-fluid">
<?php foreach ($boxes as $box => $letters) {
    $isInbox = $box === 'received'; // 收件箱显示收到时间并可回复，两栏共享同一份信件正文模板。
?>
<div class="span6">
<table class="table table-condensed table-bordered fiexd">
<tr class="<?=$isInbox ? 'info' : ''?>">
<th>ID</th><th><?=$isInbox ? '发件人' : '收件人'?></th><th><?=$isInbox ? '收件箱' : '发件箱'?></th>
<?php if ($isInbox) { ?><th>收到时间</th><?php } ?>
</tr>
<?php foreach ($letters as $letter) { ?>
<tr class="<?=$letter['readed'] ? '' : ($isInbox ? 'error' : 'success')?>">
<td><?=$letter['mid']?></td>
<td><a href="../user/detail.php?uid=<?=$letter['contact_id']?>" target="_blank">
<?=gravatar::showImage($letter['email'])?><?=h($letter['nickname'])?></a></td>
<td><a href="#<?=$box?>-mail<?=$letter['mid']?>" data-toggle="modal"
    <?php if ($isInbox) { ?>onclick="markread(<?=(int)$letter['mid']?>)"<?php } ?>><?=h($letter['title'])?></a></td>
<?php if ($isInbox) { ?><td><?=date('Y-m-d', $letter['time'])?></td><?php } ?>
</tr>
<?php } ?>
</table>
<?php // 弹窗放在表格外。自己给自己寄信时，两栏用各自的 ID 打开正确弹窗。
foreach ($letters as $letter) { ?>
<div id="<?=$box?>-mail<?=$letter['mid']?>" class="modal hide fade in">
<div class="modal-header"><button class="close" data-dismiss="modal">×</button><h3><?=h($letter['title'])?></h3></div>
<div class="modal-body"><?=nl2br(h($letter['msg']))?></div>
<div class="modal-footer">
<span class="pull-left">
<a href="../user/detail.php?uid=<?=$letter['contact_id']?>" target="_blank">
<?=gravatar::showImage($letter['email'])?><?=h($letter['nickname'])?></a>
<?=date('Y-m-d H:i:s', $letter['time'])?></span>
<?php if ($isInbox) { ?><button class="btn btn-success" onclick="reply(<?=(int)$letter['contact_id']?>, <?=(int)$letter['mid']?>)">回复</button><?php } ?>
<button data-dismiss="modal" class="btn">关闭</button>
</div></div>
<?php } ?>
</div>
<?php } ?>
</div>
<?php require_once __DIR__.'/../include/footer.php'; ?>
