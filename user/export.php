<?php
require_once("../include/header.php");
gethead(0,"sess","");
if(!有此权限('查看用户') && $_SESSION['ID']!=$_GET['uid'])
异常("没有权限，且不是本人！", 取路径("user/detail.php?uid={$_GET['uid']}"));
chdir($SET['dir_source']);
$uid=(int)$_GET['uid'];
$dir="{$uid}";
$src="tmp.zip";
exec("rm $src");
$zip="zip -r {$src} {$dir}";
exec($zip);
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=\"{$dir}.zip\"");
@readfile($src);
?>

