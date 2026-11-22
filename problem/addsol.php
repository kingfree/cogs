<?php
require_once("../include/header.php");
gethead(8, "sess", "");
$pid = (int)($_POST['pid'] ?? 0);
$title = $_POST['title'] ?? '';
$link = $_POST['link'] ?? '';
if (!$pid || !is_string($title) || trim($title) === '') 异常("请填写题目和题解名称");
if (!is_string($link) || !filter_var($link, FILTER_VALIDATE_URL) ||
    !in_array(parse_url($link, PHP_URL_SCHEME), ['http', 'https'], true)) 异常("题解链接无效");
$db = new DataAccess();
if (!$db->dosql("select pid from problem where pid=? and readforce<=?", [$pid, (int)$_SESSION['readforce']])) 异常("题目不可用");
$text = htmlspecialchars($title, ENT_QUOTES, 'UTF-8').' '.htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
$db->dosql("insert into comments(pid,uid,detail,stime,showcode) values(?,?,?,?,0)", [$pid, (int)$_SESSION['ID'], $text, time()]);
提示("题解已添加", 取路径("problem/problem.php?pid=$pid"));
