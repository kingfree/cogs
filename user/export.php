<?php
// GET /user/export.php?uid=2 导出该用户当前可读的源码。查看用户资料权限只允许进入导出页，
// 每份文件仍须通过与单条提交相同的题目阅读条件和源码分享条件。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$uid = (int)($_GET['uid'] ?? 0); // 显式选定一个账号，不允许无范围的全站源码导出。
if (!$uid || ($uid !== (int)$_SESSION['ID'] && !Privilege::allows('查看用户'))) { http_response_code(403); exit; } // 同时核对请求账号与当前访问者，再筛选可见文件。
$files = []; // 归档表按导出时仍可见的提交重新生成。
foreach (Submission::exportableNames($uid) as $name) {
    $path = Submission::sourcePath($SET['dir_source'], $uid, $name); // 校验源码确实属于该用户的存储目录。
    if ($path !== null) $files[$uid.'/'.$name] = $path;
}
Archive::download($uid.'.zip', $files); // 归档成功才启动 ZIP 下载响应。
