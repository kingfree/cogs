<?php
// 旧评测结果按题目文件名、类型及测试点下载。out 只从本人目录读取，并重新确认题目仍可见。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$parts = explode('?', base64_decode($_GET['id'] ?? '', true) ?: '');
if (count($parts) !== 3 || !TestData::name($parts[0]) || !ctype_digit($parts[2])) { http_response_code(400); exit; }
[$name, $type, $point] = $parts;
if ($type === 'out') {
    $path = Problem::readableFilename($name)
        ? Submission::sourcePath($SET['dir_source'], (int)$_SESSION['ID'], $name.$point.'.out') : null;
    if ($path === null) { http_response_code(404); exit; }
    header('Content-Type: text/plain; charset=UTF-8');
    readfile($path);
} else {
    TestData::sendCase($name, (int)$point, $type);
}
