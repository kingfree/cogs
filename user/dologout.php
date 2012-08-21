<?php
require_once("../include/header.php");

setcookie("cogs_user","",0,"/");
setcookie("cogs_pwd_hash","",0,"/");
$_SESSION=array();
session_destroy();

if(!$_COOKIE['cogs_usr'] || !$_COOKIE['cogs_pwd_hash'])
i提示("退出（注销用户会话）成功！");
else
i异常("删除 Cookies 失败！");

?>
