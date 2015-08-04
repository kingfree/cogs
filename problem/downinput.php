<?php
require_once("../include/header.php");
gethead(0,"sess","");
chdir($cfg['testdata']);
$file=$_GET['file'];
$data=(int)$_GET['data'];
if(!$file || !$data) exit;
$t = time();
$dir="/tmp/$t/$file";
$src="/tmp/input$file$t.zip";
$md="mkdir -p {$dir}";
exec($md);
chdir($dir);
for ($i=1;$i<=$data;$i++)
    exec("cp -u $file/$file$i.in .");
$zip="zip -r {$src} .";
exec($zip);
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=\"{$file}.zip\"");
readfile($src);
?>

