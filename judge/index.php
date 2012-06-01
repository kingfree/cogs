<?php
require_once("lib.php");

$query=array_decode($_POST['query']);

if (isset($query['action']))
	require("grade.php");
else
	require("welcome.php");
?>
