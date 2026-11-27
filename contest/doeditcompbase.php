<?php
// 从 editcompbase.php 提交题单；既有 cbid 会被各场次继续引用。
// 例：打开 contest/editcompbase.php?action=add，填写题单名、勾选题目并保存。
require_once __DIR__.'/../include/header.php';
gethead(0, '修改比赛', ''); // 只有题单管理员可以创建或调整比赛内容。
requirePost(); // 修改题单会影响关联场次，保存时验证表单令牌。
$action = $_POST['action'] ?? $_GET['action'] ?? ''; // 保留旧表单使用查询字符串传递操作的方式。
if (!in_array($action, ['add','edit'], true)) throw new DomainException('未知的题单操作。', 400);
$id = $action === 'edit' ? (int)($_POST['cbid'] ?? $_GET['cbid'] ?? 0) : null; // 编辑保留原题单号，创建由数据库分配编号。
$name = $_POST['cname'] ?? '';
$selection = $_POST['cons'] ?? []; // 未勾选任何题目时允许保存草稿题单。
if (!is_array($selection)) throw new DomainException('请选择题目编号。');
Contest::save($id, $name, $selection, (int)$_SESSION['ID']); // 出题人取自当前会话，顺序取自表单。
i提示(($id === null ? '添加比赛 ' : '修改比赛 ').h($name).' 成功！', 取路径('contest/compbase.php'));
