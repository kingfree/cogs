<?php
require_once("../include/header.php");
if (!isset($_POST['username'], $_POST['password']) ||
    !is_string($_POST['username']) || !is_string($_POST['password'])) {
    i异常("请输入用户名和密码。", 取路径("user/login.php"));
}
$db = new DataAccess();
$db->dosql("select * from userinfo where usr = ? limit 1", array($_POST['username']));
$user = $db->rtnrlt();
if (!$user || !hash_equals($user['pwdhash'], encode($_POST['password']))) {
    i异常("用户名或密码错误。", 取路径("user/login.php"));
}
startLogin($user);
if (!empty($_POST['savepwd'])) rememberLogin($user);
i提示("登录成功！", 取路径("user/panel.php"));
