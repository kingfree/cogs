<?php
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
