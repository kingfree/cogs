<?php
// 处理 user/editgroup.php 的分组表单。action=add 创建分组，action=edit 按 gid 更新父组及管理员。
require_once(__DIR__.'/../include/header.php');
gethead(8,"分组管理","");
requirePost();

$action = $_POST['action'] ?? $_GET['action'] ?? ''; // 同时兼容旧管理链接及表单内携带的操作名。
$name = trim($_POST['gname'] ?? ''); // 名称显示在目录、比赛报名与入组申请中，空名称无法区分分组。
$memo = $_POST['memo'] ?? '';
$owner = (int)($_POST['adminuid'] ?? 0); // 入组申请会发送给这个账号。
$parent = (int)($_POST['parent'] ?? 0); // 根层使用 0，保留旧表单的层级语义。
$return = 取路径('user/grouplist.php');
if ($name === '' || $owner < 1) 异常('分组名称或管理员无效。', $return); // 组名和负责人缺失时先留在目录，避免创建无法申请加入的组。
if ($action === 'add') {
    UserGroup::create($name, $memo, $owner, $parent); // 新组的编号由数据库分配。
    提示('添加分组 '.h($name).' 成功！', $return);
} elseif ($action === 'edit') {
    $id = (int)($_POST['gid'] ?? $_GET['gid'] ?? 0); // 编辑沿用既有编号，避免破坏成员和引用它的比赛。
    UserGroup::edit($name, $memo, $owner, $parent, $id); // 现有成员与比赛继续引用原 gid。
    提示('修改分组 '.h($name).' 成功！', $return);
} else {
    异常('未知分组操作。', $return);
}
