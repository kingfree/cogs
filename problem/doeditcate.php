<?php
// 分类管理表单的保存入口。例：从 problem/editcate.php?action=edit&caid=1 修改已有分类。
require_once __DIR__.'/../include/header.php';
gethead(8, '分类管理', '');
requirePost();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$back = 取路径('problem/catelist.php');
$name = trim($_POST['cname'] ?? '');
$memo = trim($_POST['memo'] ?? '');
if ($name === '' || mb_strlen($name) > 24) 异常('分类名须为 1 至 24 个字符。', $back);

if ($action === 'add') {
    Category::create($name, $memo);
    提示('添加分类 '.h($name).' 成功!', $back);
} elseif ($action === 'edit') {
    $categoryId = (int)($_POST['caid'] ?? $_GET['caid'] ?? 0);
    Category::edit($name, $memo, $categoryId);
    提示('编辑分类 '.h($name).' 成功!', $back);
}
