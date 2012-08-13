<?php
require_once("../include/header.php");

setcookie("cogs_user","",time()-7776000);
setcookie("cogs_pwd_hash","",time()-7776000);
$_SESSION=array();
session_destroy();

i提示("退出（注销用户会话）成功！");

?>
