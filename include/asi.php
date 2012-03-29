<?php
foreach($_GET as $k=>$v)
{
	$_GET[$k]=str_replace(" ","",$_GET[$k]);
	$_GET[$k]=str_replace("'","",$_GET[$k]);
	$_GET[$k]=str_replace("\"","",$_GET[$k]);
}
/*foreach($_POST as $k=>$v)
{
	$_POST[$k]=str_replace(" ","",$_POST[$k]);
	$_POST[$k]=str_replace("'","",$_POST[$k]);
	$_POST[$k]=str_replace("\"","",$_POST[$k]);
}*/
foreach($_REQUEST as $k=>$v)
{
	$_REQUEST[$k]=str_replace(" ","",$_REQUEST[$k]);
	$_REQUEST[$k]=str_replace("'","",$_REQUEST[$k]);
	$_REQUEST[$k]=str_replace("\"","",$_REQUEST[$k]);
}
?>