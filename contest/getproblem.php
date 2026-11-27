<?php
// 题单编辑器按题号确认题名，例如 contest/getproblem.php?pid=1；响应是纯文本。
require_once __DIR__.'/../include/header.php';
gethead(0, '修改比赛', '');
header('Content-Type: text/plain; charset=UTF-8');
echo Problem::byId((int)($_GET['pid'] ?? 0))[0]['probname'] ?? '';
