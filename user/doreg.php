<?php
// 接收 user/register.php?accept=1 的注册表单，通过图形验证码和资料校验后建立账号与会话。
require_once __DIR__.'/../include/header.php';
requirePost();
$page = 取路径('user/register.php?accept=1'); // 表单无效时返回已同意协议的注册页。
if (empty($SET['limit_regallow']) || !empty($_SESSION['ID'])) { http_response_code(403); exit('当前不能注册。'); } // 服务端复核开关，已打开的旧注册表单也受此限制。
$code = $_SESSION['IMGCODE'] ?? ''; // 校验值只取服务端为本次图片保存的内容。
// 不论本次输入是否正确，当前图片的验证码只使用一次。
unset($_SESSION['IMGCODE']);
if ($code === '' || !is_string($_POST['VerifyCode'] ?? null) || !hash_equals($code, $_POST['VerifyCode'])) i异常('验证码错误。', $page);
$password = $_POST['pwd'] ?? ''; // 密码按原输入保存，不与普通资料一起 trim。
if ($password !== ($_POST['repwd'] ?? null)) i异常('两次密码不一致。', $page);
try {
    $profile = Account::profileFields($_POST);
    $recovery = Password::recovery($_POST['passwordtip'] ?? '', $_POST['passwordtipans'] ?? ''); // 没填答案表示关闭找回；填了就只存其哈希。
    $user = Account::register($profile, Password::hash($password), $recovery, $SET); // 唯一索引负责处理同名注册，成功时拿到新 UID。
} catch (InvalidArgumentException $error) {
    i异常($error->getMessage(), $page);
}
if (!Login::start($user)) i异常('账号凭据已变更，请重新登录。', 取路径('user/login.php')); // 注册与首次登录之间账号被改动时需按最新凭据重新登录。
Login::saveToken($user); // 注册成功沿用旧站自动登录并记住当前设备的体验。
i提示('注册成功。', 取路径('user/panel.php'));
