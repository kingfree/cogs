<?php
// 将题解链接作为讨论发到可读的题目下。
// 向 problem/addsol.php 提交 pid=1、title、HTTP(S) 地址 link，以及登录会话的 form_token。
require_once("../include/header.php");
gethead(8, "sess", "");
requirePost();
$pid = (int)($_POST['pid'] ?? 0);
$title = $_POST['title'] ?? '';
$link = $_POST['link'] ?? '';
if (!$pid || !is_string($title) || trim($title) === '') 异常("请填写题目和题解名称");
if (!is_string($link) || !filter_var($link, FILTER_VALIDATE_URL) ||
    !in_array(parse_url($link, PHP_URL_SCHEME), ['http', 'https'], true)) 异常("题解链接无效");
if (!Problem::forReader($pid)) 异常("题目不可用");
$text = htmlspecialchars($title, ENT_QUOTES, 'UTF-8').' '.htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
Comment::addSolution($pid, (int)$_SESSION['ID'], $text, time());
提示("题解已添加", 取路径("problem/problem.php?pid=$pid"));
