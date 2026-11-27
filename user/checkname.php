<?php
// 个人资料表单按用户名查重，例如 user/checkname.php?name=student；返回现有用户编号，未找到时返回 0。
require_once __DIR__.'/../include/header.php';
header('Content-Type: text/plain; charset=UTF-8');
$name = $_GET['name'] ?? ''; // 此兼容查询只返回占用该登录名的 UID，空输入表示未选名称。
echo $name === '' ? 0 : (Account::byUsernameId($name)[0]['uid'] ?? 0);
