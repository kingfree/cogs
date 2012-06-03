<?php
require_once("stdlib.php");

$query=array_decode($_POST['query']);

if (isset($query['action']))
	require("grade.php");
else
	require("welcome.php");
?>
