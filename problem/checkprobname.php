<?php
require_once("../include/header.php");
$name = $_GET['name'];
$problems = Problem::byTitle($name);
$rows = count($problems);
echo $rows;
?>
