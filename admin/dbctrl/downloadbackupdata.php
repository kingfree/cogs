<?php
require_once("../../include/stdhead.php");
gethead(0,"advadmin","");

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"{$_GET[src]}\"");
$src=$SETTINGS['dir_databackup']."/".$_GET[src];
@readfile($src);
?>