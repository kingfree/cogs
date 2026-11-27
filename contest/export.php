<?php
// 有查看比赛权限时访问 contest/export.php?ctid=1 下载场次源码 ZIP，压缩包内按选手编号分目录。
require_once __DIR__.'/../include/header.php';
gethead(0, '查看比赛', ''); // 整个场次的批量导出只开放给有评测权限的用户。
$id = (int)($_GET['ctid'] ?? 0); // 按场次隔离打包，同一题单的另一场不会混入。
Archive::download("COGS_$id.zip", ContestEntry::sourceFiles($id, $SET['dir_competition']));
