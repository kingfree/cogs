<?php
require_once("../include/header.php");
require_once(dirname(__FILE__)."/message.php");
gethead(1,"sess","邮件");
$uid = (int)$_SESSION['ID'];
$p = new DataAccess();
$d = mailReadMessage($p, $_GET['mid'], $uid);
if (!$d) 异常("邮件不存在或无权查看！");
mailMarkRead($p, $d['mid'], $uid);
?>

<div class='container-fluid'>
<table border=2>
<tr>
<th>邮件主题</th>
<td width="80%"><?=mailHtml($d['title'])?></td>
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
<input name="toid" type="hidden" value=<?=$d['fromid']?> />
<input name="title" type="hidden" value="回(<?=$d['mid']?>)：<?=mailHtml($d['title'])?>" />
<input type="submit" name="Submit" value="回复该邮件" class="btn"/>
</form>

</div>

<?php
include_once("../include/footer.php");
?>

