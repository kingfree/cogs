<?php
// 将题解链接作为讨论发到可读的题目下。
// 向 problem/addsol.php 提交 pid=1、title、HTTP(S) 地址 link，以及登录会话的 form_token。
require_once(__DIR__.'/../include/header.php');
gethead(8, "sess", "");
requirePost();
$pid = (int)($_POST['pid'] ?? 0);
// 标题和访问链接一起发布在该题的讨论中。
$title = $_POST['title'] ?? '';
$link = $_POST['link'] ?? '';
if (!$pid || !is_string($title) || trim($title) === '') 异常("请填写题目和题解名称");
// 题解链接仅接受可解析的 HTTP 或 HTTPS 地址。
if (!is_string($link) || !filter_var($link, FILTER_VALIDATE_URL) ||
    !in_array(parse_url($link, PHP_URL_SCHEME), ['http', 'https'], true)) 异常("题解链接无效");
if (!Problem::forReader($pid)) 异常("题目不可用");
$text = trim($title).' '.$link;             // 讨论按原文保存，BBCode 在显示时统一转义。
Comment::addSolution($pid, (int)$_SESSION['ID'], $text, time());
提示("题解已添加", 取路径("problem/problem.php?pid=$pid"));
