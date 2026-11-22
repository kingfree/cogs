<?php
require_once("../include/header.php");
if (!isset($_POST['username'], $_POST['password']) ||
    !is_string($_POST['username']) || !is_string($_POST['password'])) {
    i异常("请输入用户名和密码。", 取路径("user/login.php"));
}
$user = Account::byUsername($_POST['username'])[0] ?? false;
if (!$user || !Password::verify($_POST['password'], $user['pwdhash'])) {
    i异常("用户名或密码错误。", 取路径("user/login.php"));
}
if (password_needs_rehash($user['pwdhash'], PASSWORD_DEFAULT) && strlen($_POST['password']) <= 72) {
    $hash = Password::hash($_POST['password']);
    if (Account::upgradePassword($hash, $user['uid'], $user['pwdhash'])) $user['pwdhash'] = $hash;
    else i异常('账号凭据已变更，请重新登录。');
}
Login::start($user);
if (!empty($_POST['savepwd'])) Login::saveToken($user);
i提示("登录成功！", 取路径("user/panel.php"));
