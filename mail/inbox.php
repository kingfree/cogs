<?php
// 收发双方登录后可通过 mail/inbox.php?mid=1 查看这一封信；仅收件人打开会将它标为已读。
require_once(__DIR__.'/../include/header.php');
gethead(1,"sess","邮件");
$message = Message::open((int)($_GET['mid'] ?? 0), (int)$_SESSION['ID']); // 阅读和收件人已读状态一起处理。
if (!$message) 异常('邮件不存在或无权查看！'); // 未投递给当前账号和不存在的邮件使用同一种提示。
?>

<div class='container-fluid'>
<table border=2>
<tr>
<th>邮件主题</th>
<td width="80%"><?=h($message['title'])?></td>
</tr>
<tr>
<th>发件人ID</th>
<td><?=$message['fromid']?></td>
</tr>
<tr>
<th>收件人ID</th>
<td><?=$message['toid']?></td>
</tr>
<tr>
<th>发送时间</th>
<td><?=date('Y-m-d H:i:s',$message['time'])?></td>
</tr>
<tr>
<th>邮件内容</th>
<td><div class="MainText" width=400px><?=nl2br(sp2n(h($message['msg'])))?></div></td>
</tr>
</table>

<form id="form" name="form" method="post" action="index.php"><?=formTokenField()?>
<input name="toid" type="hidden" value=<?=$message['fromid']?> />
<input name="title" type="hidden" value="回(<?=$message['mid']?>)：<?=h($message['title'])?>" />
<input type="submit" name="Submit" value="回复该邮件" class="btn"/>
</form>

</div>

<?php
include_once(__DIR__.'/../include/footer.php');
?>

