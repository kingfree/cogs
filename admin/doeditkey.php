<?php
require_once("../include/header.php");
gethead(8, "参数设置", "");
requirePost();
if (!isset($_POST['value']) || !is_string($_POST['value'])) 异常("参数值无效");
$id = (int)$_REQUEST['ssid'];
Setting::change($_POST['value'], $id);
提示("修改参数成功！", 取路径("admin/settings.php?settings=settings"));
