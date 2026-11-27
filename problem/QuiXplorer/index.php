<?php
// 旧数据浏览器的书签转到 COGS 自己的权限入口。例：problem/QuiXplorer/index.php?dir=sum 会转到对应数据目录。
$folder = $_GET['dir'] ?? '';
header('Location: ../testdata.php?problem='.rawurlencode(is_string($folder) ? $folder : ''), true, 301);
