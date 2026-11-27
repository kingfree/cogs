<?php
// 访问 user/lost.php 开始找回密码。先 POST 用户名获取提示问题，再向同一地址提交用户名和答案。
require_once("../include/header.php");
gethead(1,"","找回密码");
if (empty($_POST['User'])&&empty($_POST['ans'])) {
?>
<form method="post" action="lost.php" class='form-inline center'><?=formTokenField()?>
<p>第一步</p>
<p>请输入您的用户名
<input name="User" type="text" id="User" />
</p>
<p>
<button type="submit" class='btn'>下一步</button>
</p>
</form>
<?php
} else {
if (empty($_POST['ans'])) {
?>
<form method="post" action="lost.php" class='form-inline center'><?=formTokenField()?>
<p>第二步</p>
<p>密码提示问题：<span class='label'>
<?php
$accounts = Account::recoveryQuestion($_POST['User']);
$cnt = count($accounts);
if ($cnt==0)
    异常("用户不存在！",取路径("user/lost.php"));
else
{
$d=($accounts[0] ?? false);
echo h($d['pwdtipques']).'?';
?></span></p>
<p>上面问题的答案
<input name="ans" type="text" id="ans" />
</p>
<p>
<button type="submit" class='btn'>下一步</button>
<input name="User" type="hidden" id="User" value="<?=h($_POST['User'] ?? '')?>" />
</p>
</form>
<?php
}
} else {
$d = Account::recoveryAnswer($_POST['User'])[0] ?? false;
if ($d && Password::verify($_POST['ans'], $d['pwdtipanshash'])) {
// 给答对问题的用户发随机临时密码；登录后可在控制面板自行修改。
$temporary = bin2hex(random_bytes(12));
Account::resetPassword(Password::hash($temporary), $_POST['User']);
header('Cache-Control: no-store');
提示('临时密码为 '.h($temporary).'，登录后请更改密码。');
}
else
异常("密码提示问题的答案不正确！", 取路径("user/lost.php"));
}
}
include_once("../include/footer.php");
?>
