<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$parts = explode('?', base64_decode($_GET['id'] ?? '', true) ?: '');
if (count($parts) !== 3 || !TestData::name($parts[0]) || !ctype_digit($parts[2])) { http_response_code(400); exit; }
[$name, $type, $point] = $parts;
if ($type === 'out') {
    $path = rtrim($SET['dir_source'], '/').'/'.(int)$_SESSION['ID'].'/'.$name.$point.'.out';
    if (!is_file($path) || is_link($path)) { http_response_code(404); exit; }
    header('Content-Type: text/plain; charset=UTF-8');
    readfile($path);
} else {
    TestData::sendCase($name, (int)$point, $type);
}
