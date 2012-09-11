<?php
require_once("../include/header.php");
gethead(0,"sess","");
$str=$_GET['id'];
$str=base64_decode($str);
$arr=explode("?",$str);
if ($arr[1]=="out")
	$src="{$SET['dir_source']}/{$_SESSION[ID]}/{$arr[0]}{$arr[2]}.out";
else
	$src="{$cfg['dir_testdata']}/{$arr[0]}/{$arr[0]}{$arr[2]}.{$arr[1]}";
header("Content-type: text/plain");
@readfile($src);
?>
