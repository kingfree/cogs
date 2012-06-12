<?php
require_once("../include/header.php");
gethead(0,"查看比赛","");
$ctid=(int)$_GET['ctid'];
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename='{$ctid}.zip'");
chdir($SET['dir_competition']);
$src="{$ctid}.zip";
if(file_exists($src)) unlink($src);
$zip="zip -r {$src} {$ctid}/";
exec($zip);
@readfile($src);
?>

