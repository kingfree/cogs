<?php
require_once("../include/stdhead.php");
gethead(0,"verfy","");
$LIB->get_userinfo($_SESSION['ID']);
if ($_SESSION['admin']!=-1)
	header("location: /{$SETTINGS['global_root']}");

$p=new DataAccess();
$sql="update userinfo set email='{$_POST['email']}' where uid={$_SESSION['ID']}";
$p->dosql($sql);
$LIB->get_userinfo($_SESSION['ID']);
$LIB->sendverfymail($_SESSION['ID']);
echo '<script>document.location="verfy.php"</script>';
?>