<?php
// GET /user/export.php?uid=2 导出该用户当前可读的源码。查看用户资料权限只允许进入导出页，
// 每份文件仍须通过与单条提交相同的题目阅读条件和源码分享条件。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$uid = (int)($_GET['uid'] ?? 0);
if (!$uid || ($uid !== (int)$_SESSION['ID'] && !Privilege::allows('查看用户'))) { http_response_code(403); exit; }
$files = [];
foreach (Submission::exportableNames($uid) as $name) {
    $path = Submission::sourcePath($SET['dir_source'], $uid, $name);
    if ($path !== null) $files[$uid.'/'.$name] = $path;
}
Archive::download($uid.'.zip', $files);
