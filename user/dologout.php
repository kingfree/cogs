<?php
require_once("../include/header.php");

setcookie("cojs_login",$d['pwdhash'], time()-7776000);
setcookie("User",$_POST['User'], time()-7776000);
$_SESSION=array();
session_destroy();

i提示("退出（注销用户会话）成功！");

?>
