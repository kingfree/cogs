<?php
require_once("../include/header.php");
gethead(8,"分类管理","");
requirePost();

if (($_POST['action'] ?? $_GET['action'] ?? '')=='add') {
	Category::create($_POST['cname'], $_POST['memo']);
    提示("添加分类 {$_POST['cname']} 成功!",取路径("problem/catelist.php"));
}

if (($_POST['action'] ?? $_GET['action'] ?? '')=='edit') {
	Category::edit($_POST['cname'], $_POST['memo'], ($_POST['caid'] ?? $_GET['caid'] ?? ''));
    提示("编辑分类 {$_POST['cname']} 成功!",取路径("problem/catelist.php"));
}
?>
