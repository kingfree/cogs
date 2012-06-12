<?php
require_once("../include/header.php");
gethead(8,"分组管理","");

if ($_REQUEST[action]=='add')
{
	$p=new DataAccess();
	$sql="insert into groups(gname,memo,adminuid,parent) values('{$_POST[gname]}','{$_POST[memo]}','{$_POST['adminuid']}','{$_POST['parent']}')";
	$p->dosql($sql);
    提示("添加分组 {$_POST[gname]} 成功！",取路径("user/grouplist.php"));
}

if ($_REQUEST[action]=='edit')
{
	$p=new DataAccess();
	$sql="update groups set gname='{$_POST[gname]}',memo='{$_POST[memo]}',adminuid='{$_POST['adminuid']}',parent='{$_POST['parent']}' where gid={$_REQUEST[gid]}";
	$p->dosql($sql);
    提示("修改分组 {$_POST[gname]} 成功！",取路径("user/grouplist.php"));
}
?>
