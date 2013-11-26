<?php
require_once("../include/header.php");

setcookie("cogs_user","",-100,"/");
setcookie("cogs_pwd_hash","",-100,"/");
$_SESSION=array();
session_destroy();

if(!isset($_SESSION['ID']))
i提示("退出（注销用户会话）成功！");
else
i异常("退出（注销用户会话）失败！");

?>
