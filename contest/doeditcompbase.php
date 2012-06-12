<?php
require_once("../include/header.php");
gethead(8,"修改比赛","");

if ($_REQUEST[action]=='add')
{
	$p=new DataAccess();
	@$cons=implode(":",$_POST[cons]);
	$sql="insert into compbase(cname,contains,ouid) values('{$_POST[cname]}','{$cons}',{$_SESSION[ID]})";
	$p->dosql($sql);
    提示("添加比赛 {$_POST[cname]} 成功！",取路径("contest/compbase.php"));
}

if ($_REQUEST[action]=='edit')
{
	$p=new DataAccess();
	@$cons=implode(":",$_POST[cons]);
	$sql="update compbase set cname='{$_POST[cname]}',contains='{$cons}' where cbid={$_REQUEST[cbid]}";	
	$p->dosql($sql);
    提示("修改比赛 {$_POST[cname]} 成功！",取路径("contest/compbase.php"));
}
?>
