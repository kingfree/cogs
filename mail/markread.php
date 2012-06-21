<?php
require_once("../include/header.php");
$p=new DataAccess();
$mid = $_GET['mid'];
$uid = $_SESSION[ID];
$sql = "update mail set readed = 1 where mid = $mid and toid = $uid";
$p->dosql($sql);
?>

