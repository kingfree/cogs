<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$uid = (int)($_GET['uid'] ?? 0);
if (!$uid || ($uid !== (int)$_SESSION['ID'] && !Privilege::allows('查看用户'))) { http_response_code(403); exit; }
$files = [];
$dir = rtrim($SET['dir_source'], '/').'/'.$uid;
foreach (glob($dir.'/*') ?: [] as $path) {
    if (is_file($path) && !is_link($path)) $files[$uid.'/'.basename($path)] = $path;
}
Archive::download($uid.'.zip', $files);
