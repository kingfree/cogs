<?php
require_once("../include/header.php");
gethead(0,"","");
chdir($cfg['testdata']);
$file=$_POST['filename'];
$io=$_POST['io'];
if(!$io) $io="in";
$point=(int)$_POST['point'];
$src="$file/$file$point.$io";
header("Content-type: text/plain; charset=utf-8");
header("Content-Disposition: attachment; filename=$file$point.$io");
//header("Content-Disposition: inline; filename=$file$point.$io");
@readfile($src);
?>

