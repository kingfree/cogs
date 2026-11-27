<?php
// 跳转到一道可读且当前账号尚未通过的题目，没有匹配题目时回到题库。
// 浏览器打开 problem/random.php，最终题号由站点选择。
require_once __DIR__.'/../include/header.php';
$pid = Problem::randomUnsolved($_SESSION['ID']);
if ($pid === null) i异常("没有可以随机选择的未通过题目。", 取路径('problem/index.php'));
header('Location: problem.php?pid='.$pid);
