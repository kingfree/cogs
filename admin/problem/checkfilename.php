<?php
require_once("../../include/stdhead.php");
gethead(0,"admin","");
$p=new DataAccess();
$name = $_GET['name'];
$sql = "SELECT pid FROM problem WHERE filename='$name'";
$rows = $p->dosql($sql);
echo $rows;
?>
