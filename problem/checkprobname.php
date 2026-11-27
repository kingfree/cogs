<?php
// 检查题库里有多少道同名题目，供编辑表单提示标题重复。
// GET problem/checkprobname.php?name=Sum，响应为纯数字。
require_once("../include/header.php");
$name = $_GET['name'];
$problems = Problem::byTitle($name);
$rows = count($problems);
echo $rows;
?>
