<?php
require_once("../include/header.php");
gethead(8,"分类管理","");

if ($_REQUEST['action']=='add') {
	$p=new DataAccess();
	$sql = new SqlQuery("insert into category(cname,memo) values(?,?)", [$_POST['cname'], $_POST['memo']]);
	$p->dosql($sql);
    提示("添加分类 {$_POST['cname']} 成功!",取路径("problem/catelist.php"));
}

if ($_REQUEST['action']=='edit') {
	$p=new DataAccess();
	$sql = new SqlQuery("update category set cname=?,memo=? where caid=?", [$_POST['cname'], $_POST['memo'], $_REQUEST['caid']]);
	$p->dosql($sql);
    提示("编辑分类 {$_POST['cname']} 成功!",取路径("problem/catelist.php"));
}
?>
