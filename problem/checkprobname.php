<?php
require_once("../include/header.php");
$p=new DataAccess();
$name = $_GET['name'];
$sql = "SELECT pid FROM problem WHERE probname='$name'";
$rows = $p->dosql($sql);
echo $rows;
?>
