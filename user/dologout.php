<?php
// 退出入口从导航提交 POST；GET 打开确认表单。注销当前会话并清除浏览器保存的登录凭据。
require_once("../include/header.php");

requirePost();
Login::forget();
// 同时清掉旧版站点曾设置的身份 Cookie。
setcookie("cogs_usr","",-100,"/");
setcookie("cogs_pwd_hash","",-100,"/");
$_SESSION=array();
session_destroy();

if(!isset($_SESSION['ID']))
i提示("退出（注销用户会话）成功！");
else
i异常("退出（注销用户会话）失败！");

?>
