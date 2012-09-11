<?php
require_once("../include/header.php");
gethead(8,"管理评测","");
$LIB->func_socket();

if ($_POST['enabled']==1) $enabled=1; else $enabled=0;

if ($_REQUEST[action]=='add')
{
	$p=new DataAccess();
	$sql="insert into grader(address,priority,enabled,memo) values('{$_POST['address']}','{$_POST['priority']}','{$enabled}','{$_POST[memo]}')";
	$p->dosql($sql);
    提示("添加评测机 {$_POST['address']} 成功！", 取路径("submit/graderlist.php"));
}

if ($_REQUEST['action']=='edit')
{
	$p=new DataAccess();
	$sql="update grader set address='{$_POST['address']}',priority='{$_POST['priority']}',enabled='{$enabled}',memo='{$_POST['memo']}' where grid={$_REQUEST[grid]}";
	$p->dosql($sql);
    提示("修改评测机 {$_POST['address']} 成功！", 取路径("submit/graderlist.php"));
}

if ($_REQUEST['action']=='start')
{
	$p=new DataAccess();
	$sql="select address from grader where grid={$_GET[grid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$s['action']="start";
	httpsocket($d['address'],$s);
    提示("启动评测机 {$_GET[grid]} 成功！", 取路径("submit/graderlist.php"));
}

if ($_REQUEST['action']=='stop')
{
	$p=new DataAccess();
	$sql="select address from grader where grid={$_GET[grid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$s['action']="shutdown";
	httpsocket($d['address'],$s);
    提示("关闭评测机 {$_GET[grid]} 成功！", 取路径("submit/graderlist.php"));
}
?>
