<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '查看比赛', '');
$id = (int)($_GET['ctid'] ?? 0);
$files = [];
foreach (ContestEntry::sourceFiles($id) as $entry) {
    $uid = (int)$entry['uid'];
    $name = $entry['filename'];
    if (!TestData::name($name)) continue;
    foreach (glob(rtrim($SET['dir_competition'], '/')."/$id/$uid/$name.*") ?: [] as $path) {
        if (is_file($path) && !is_link($path)) $files[$uid.'/'.basename($path)] = $path;
    }
}
Archive::download("COGS_$id.zip", $files);
