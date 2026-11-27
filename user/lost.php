<?php
// 访问 user/lost.php 后先提交用户名查看提示问题，再提交答案领取临时密码。
require_once __DIR__.'/../include/header.php';
$username = $_POST['User'] ?? ''; // 两步表单都靠登录名定位同一账号。
$answer = $_POST['ans'] ?? ''; // 没有答案的请求只读取可以公开提示的问题。
// 响应页带临时凭据，必须在输出页头前禁止浏览器缓存。
if ($answer !== '') header('Cache-Control: no-store');
gethead(1, '', '找回密码');
if ($answer !== '') {
    $temporary = Account::recoverPassword($username, $answer); // 核对并替换密码后才会得到一次性显示的明文。
    if ($temporary === null) 异常('密码提示问题的答案不正确！', 取路径('user/lost.php')); // 校验失败时不展示临时密码，也不改变账号凭据。
    提示('临时密码为 '.h($temporary).'，登录后请更改密码。');
} elseif ($username !== '') {
    $question = Account::recoveryQuestion($username); // 提问阶段不向页面返回答案或旧密码哈希。
    if ($question === null) 异常('用户不存在！', 取路径('user/lost.php'));
?>
<form method="post" action="lost.php" class="form-inline center">
<?=formTokenField()?>
<p>第二步</p>
<p>密码提示问题：<span class="label"><?=h($question)?>?</span></p>
<p>上面问题的答案 <input name="ans" type="text" id="ans" /></p>
<input name="User" type="hidden" value="<?=h($username)?>" />
<button type="submit" class="btn">下一步</button>
</form>
<?php } else { ?>
<form method="post" action="lost.php" class="form-inline center">
<?=formTokenField()?>
<p>第一步</p>
<p>请输入您的用户名 <input name="User" type="text" id="User" /></p>
<button type="submit" class="btn">下一步</button>
</form>
<?php }
require_once __DIR__.'/../include/footer.php';
