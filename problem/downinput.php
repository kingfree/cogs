<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
$name = $_GET['file'] ?? '';
$problem = TestData::name($name) ? (Problem::byFilename($name)[0] ?? null) : null;
$problem = $problem ? (Problem::byId($problem['pid'])[0] ?? null) : null;
if (!$problem || (!Privilege::allows('查看数据') && (!in_array((int)$problem['plugin'], [3, 4], true)
    || !$problem['submitable'] || $problem['readforce'] > $_SESSION['readforce']
    || !UserGroup::includes($problem['group'], $_SESSION['group'])))) {
    http_response_code(403);
    exit;
}
$files = [];
for ($i = 1; $i <= $problem['datacnt']; $i++) {
    $entry = $name.$i.'.in';
    $path = TestData::file($name, $entry);
    if (is_file($path)) $files[$entry] = $path;
}
Archive::download($name.'.zip', $files);
