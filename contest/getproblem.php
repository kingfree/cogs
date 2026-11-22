<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '修改比赛', '');
header('Content-Type: text/plain; charset=UTF-8');
echo Problem::byId((int)($_GET['pid'] ?? 0))[0]['probname'] ?? '';
