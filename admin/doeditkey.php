<?php
require_once("../include/header.php");
gethead(8,"admin","");
过滤();

$p=new DataAccess();
$sql="update settings set value='{$_POST[value]}' where ssid={$_REQUEST[ssid]}";
$p->dosql($sql);

提示("修改参数<code>{$_REQUEST[ssid]}</code>成功！",取路径("admin/settings.php?settings=settings"));

?>
