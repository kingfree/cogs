<?php
// 旧数据浏览器的书签转到 COGS 自己的权限入口。
$folder = $_GET['dir'] ?? '';
header('Location: ../testdata.php?problem='.rawurlencode(is_string($folder) ? $folder : ''), true, 301);
