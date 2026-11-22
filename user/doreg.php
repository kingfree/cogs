<?php
require_once __DIR__.'/../include/header.php';
requirePost();
$page = 取路径('user/register.php?accept=1');
if (empty($SET['limit_regallow']) || !empty($_SESSION['ID'])) { http_response_code(403); exit('当前不能注册。'); }
$code = $_SESSION['IMGCODE'] ?? '';
unset($_SESSION['IMGCODE']);
if ($code === '' || !is_string($_POST['VerifyCode'] ?? null) || !hash_equals($code, $_POST['VerifyCode'])) i异常('验证码错误。', $page);
$name = trim($_POST['usr'] ?? '');
$nickname = trim($_POST['nickname'] ?? '');
$email = trim($_POST['email'] ?? '');
$realName = trim($_POST['realname'] ?? '');
$password = $_POST['pwd'] ?? '';
if (!preg_match('/^[A-Za-z0-9_]{1,24}$/D', $name) || $nickname === '' || mb_strlen($nickname) > 16 ||
    mb_strlen($realName) > 16 || !filter_var($email, FILTER_VALIDATE_EMAIL)) i异常('用户名、昵称或邮箱格式无效。', $page);
if ($password !== ($_POST['repwd'] ?? null)) i异常('两次密码不一致。', $page);
try {
    $hash = Password::hash($password);
    $question = trim($_POST['passwordtip'] ?? '');
    if (mb_strlen($question) > 64) throw new InvalidArgumentException('提示问题过长。');
    $answer = $_POST['passwordtipans'] ?? '';
    $answer = $answer === '' ? '' : Password::hash($answer);
    Account::register($name, $nickname, (int)($SET['reg_readforce'] ?? $SET['reg_readfroce'] ?? 0), time(), $hash, $question,
        $answer, (string)($_POST['memo'] ?? ''), $realName, $email, $SET['reg_defgroup'], $SET['user_style']);
} catch (InvalidArgumentException $error) { i异常($error->getMessage(), $page); }
catch (PDOException $error) {
    if ($error->getCode() === '23000') i异常('用户名已存在。', $page);
    throw $error;
}
$user = ['uid'=>(int)Database::connection()->lastInsertId(), 'pwdhash'=>$hash];
Login::start($user);
Login::saveToken($user);
i提示('注册成功。', 取路径('user/panel.php'));
