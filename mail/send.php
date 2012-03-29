<?php
require_once("../include/stdhead.php");
gethead(0,"sess","");

$p=new DataAccess();
$sql = "insert into mail(mid, fromid, toid, time, readed, title, msg) values(0, {$_POST['fromid']}, {$_POST['toid']}, ".time().", 0, '{$_POST['title']}', '{$_POST['msg']}')";
$p->dosql($sql);// or die(mysql_error());
header("Location:../refresh.php?id=22");

?>
