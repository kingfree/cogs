<?php
// 旧评测结果按题目文件名、类型及测试点下载。out 只从本人目录读取，并重新确认题目仍可见。
// 例：路径('submit/downloaddata.php?id='.rawurlencode(base64_encode('sum?out?1'))) 指向登录者自己的第一个输出文件。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$parts = explode('?', base64_decode($_GET['id'] ?? '', true) ?: '');   // 旧下载链接依次编码题名、数据类型和点号。
if (count($parts) !== 3 || !TestData::name($parts[0]) || !ctype_digit($parts[2])) { http_response_code(400); exit; }   // 拒绝缺字段、非法题名或非数字点号。
[$name, $type, $point] = $parts;
if ($type === 'out') {   // 选手输出只读本人目录，输入与答案走另一种授权。
    $path = Problem::readableFilename($name)
        ? Submission::sourcePath($SET['dir_source'], (int)$_SESSION['ID'], $name.$point.'.out') : null;   // 仅允许定位本人这道可见题的普通文件。
    if ($path === null) { http_response_code(404); exit; }   // 题目不可读和本人没有输出文件使用相同答复。
    header('Content-Type: text/plain; charset=UTF-8');
    readfile($path);
} else {
    TestData::sendCase($name, (int)$point, $type);   // 输入与标准答案还要检查测试数据权限。
}
