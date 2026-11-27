<?php
// 收发双方登录后可通过 mail/inbox.php?mid=1 查看这一封信；仅收件人打开会将它标为已读。
require_once("../include/header.php");
gethead(1,"sess","邮件");
$uid = (int)$_SESSION['ID'];
$d = Message::visible((int)$_GET['mid'], $uid, $uid)[0] ?? null;
if (!$d) 异常("邮件不存在或无权查看！");
// 作者打开已发邮件时不应替收件人清掉未读标记，更新语句会核对收件人。
Message::markRead($d['mid'], $uid);
?>

<div class='container-fluid'>
<table border=2>
<tr>
<th>邮件主题</th>
<td width="80%"><?=h($d['title'])?></td>
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

<form id="form" name="form" method="post" action="index.php"><?=formTokenField()?>
<input name="toid" type="hidden" value=<?=$d['fromid']?> />
<input name="title" type="hidden" value="回(<?=$d['mid']?>)：<?=h($d['title'])?>" />
<input type="submit" name="Submit" value="回复该邮件" class="btn"/>
</form>

</div>

<?php
include_once("../include/footer.php");
?>

