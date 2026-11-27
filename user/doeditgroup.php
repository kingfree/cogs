<?php
// 处理 user/editgroup.php 的分组表单。action=add 创建分组，action=edit 按 gid 更新父组及管理员。
require_once("../include/header.php");
gethead(8,"分组管理","");
requirePost();

if (($_POST['action'] ?? $_GET['action'] ?? '')=='add')
{
	UserGroup::create($_POST['gname'], $_POST['memo'], $_POST['adminuid'], $_POST['parent']);
    提示("添加分组 {$_POST['gname']} 成功！",取路径("user/grouplist.php"));
}

if (($_POST['action'] ?? $_GET['action'] ?? '')=='edit')
{
	UserGroup::edit($_POST['gname'], $_POST['memo'], $_POST['adminuid'], $_POST['parent'], ($_POST['gid'] ?? $_GET['gid'] ?? ''));
    提示("修改分组 {$_POST['gname']} 成功！",取路径("user/grouplist.php"));
}
?>
