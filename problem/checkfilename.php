<?php
require_once("../include/header.php");
$name = $_GET['name'];
$problems = Problem::byFilename($name);
$rows = count($problems);
echo $rows;
?>
