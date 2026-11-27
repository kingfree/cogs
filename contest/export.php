<?php
// 有查看比赛权限时访问 contest/export.php?ctid=1 下载场次源码 ZIP，压缩包内按选手编号分目录。
require_once __DIR__.'/../include/header.php';
gethead(0, '查看比赛', '');
$id = (int)($_GET['ctid'] ?? 0);
$files = [];
foreach (ContestEntry::sourceFiles($id) as $entry) {
    $uid = (int)$entry['uid'];
    $name = $entry['filename'];
    if (!TestData::name($name)) continue;
    // 更换提交语言后，旧源码可能仍在磁盘上；导出只打包该条交卷登记的文件。
    $extension = Language::TYPES[$entry['lang']][0] ?? null;
    if (!$extension) continue;
    $file = "$name.$extension";
    $path = rtrim($SET['dir_competition'], '/')."/$id/$uid/$file";
    if (is_file($path) && !is_link($path)) $files[$uid.'/'.$file] = $path;
}
Archive::download("COGS_$id.zip", $files);
