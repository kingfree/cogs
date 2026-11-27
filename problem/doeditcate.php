<?php
// 分类管理表单的保存入口。例：从 problem/editcate.php?action=edit&caid=1 修改已有分类。
require_once __DIR__.'/../include/header.php';
gethead(8, '分类管理', '');
requirePost();
// 旧表单把动作写在 URL，新表单也可将同一个参数提交在正文。
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$back = 取路径('problem/catelist.php');
// 名称和说明在编辑状态下原样回填，入库时只清除首尾空白。
$name = trim($_POST['cname'] ?? '');
$memo = trim($_POST['memo'] ?? '');
// 分类名受数据库字段长度限制；说明可留空。
if ($name === '' || mb_strlen($name) > 24) 异常('分类名须为 1 至 24 个字符。', $back);

if ($action === 'add') {
    Category::create($name, $memo);
    // 提示支持富文本，分类名在拼进提示时单独转义。
    提示('添加分类 '.h($name).' 成功!', $back);
} elseif ($action === 'edit') {
    // 更新按分类编号定位，所有使用该分类的题目随后显示新名称。
    $categoryId = (int)($_POST['caid'] ?? $_GET['caid'] ?? 0);
    Category::edit($name, $memo, $categoryId);
    提示('编辑分类 '.h($name).' 成功!', $back);
}
