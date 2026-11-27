<?php
// 打包下载指定题目的测试输入。提交答案类题目对满足分组和阅读等级的登录用户开放。
// problem/downinput.php?file=sum 按题目登记的测试点数收集 sum1.in 等输入文件。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$name = $_GET['file'] ?? '';
// 老下载地址只记录英文文件名，用数据库登记的题目找测试点数。
$problem = TestData::name($name) ? (Problem::byFilename($name)[0] ?? null) : null;
$problem = $problem ? (Problem::byId($problem['pid'])[0] ?? null) : null;
// 有查看数据权限的账号可直接下载；选手只能下载对自己开放的提交答案题。
if (!$problem || (!Privilege::allows('查看数据') && (!in_array((int)$problem['plugin'], [3, 4], true)
    || !$problem['submitable'] || $problem['readforce'] > $_SESSION['readforce']
    || !UserGroup::includes($problem['group'], $_SESSION['group'])))) {
    http_response_code(403);
    exit;
}
// 只有取得本题下载权才开始定位文件，未授权请求不扫描目录。
$files = [];
// 从 1 起取已登记的输入文件；标准答案和特判不放进选手下载包。
for ($i = 1; $i <= $problem['datacnt']; $i++) {
    $entry = $name.$i.'.in';
    $path = TestData::file($name, $entry);
    // 未上传的测试点没有可下载内容，不创建空文件。
    if (is_file($path)) $files[$entry] = $path;
}
Archive::download($name.'.zip', $files);
