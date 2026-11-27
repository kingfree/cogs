<?php
// 给题目编辑表单做文件名查重；响应是已有题目的条数。
// GET problem/checkfilename.php?name=sum，返回 0 表示尚未使用。
require_once(__DIR__.'/../include/header.php');
echo count(Problem::byFilename($_GET['name'] ?? ''));
