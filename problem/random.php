<?php
require_once __DIR__.'/../include/header.php';
$pid = Problem::randomUnsolved($_SESSION['ID']);
if ($pid === null) i异常("没有可以随机选择的未通过题目。", 取路径('problem/index.php'));
header('Location: problem.php?pid='.$pid);
