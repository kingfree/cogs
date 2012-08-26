<?php
require_once("../include/header.php");
$p=new DataAccess();
$pid = $_GET['pid'];
$sql = "SELECT probname FROM problem WHERE pid='$pid'";
$p->dosql($sql);
$d=$p->rtnrlt(0);
echo $d['probname'];
?>
