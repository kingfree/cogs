<?php
require_once("../include/header.php");
$p=new DataAccess();
$name = $_GET['name'];
$sql = "SELECT pid FROM problem WHERE filename='$name'";
$rows = $p->dosql($sql);
echo $rows;
?>
