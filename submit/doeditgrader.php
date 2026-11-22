<?php
require_once("../include/header.php");
gethead(8,"管理评测","");
requirePost();

require_once __DIR__.'/../include/socket.php';

if ($_POST['enabled']==1) $enabled=1; else $enabled=0;

if (($_POST['action'] ?? $_GET['action'] ?? '')=='add')
{
	Judge::create($_POST['address'], $_POST['priority'], $enabled, $_POST['memo']);
    提示("添加评测机 {$_POST['address']} 成功！", 取路径("submit/graderlist.php"));
}

if (($_POST['action'] ?? $_GET['action'] ?? '')=='edit')
{
	Judge::edit($_POST['address'], $_POST['priority'], $enabled, $_POST['memo'], ($_POST['grid'] ?? $_GET['grid'] ?? ''));
    提示("修改评测机 {$_POST['address']} 成功！", 取路径("submit/graderlist.php"));
}

if (($_POST['action'] ?? $_GET['action'] ?? '')=='start')
{
	$judges = Judge::address($_GET['grid']);
	$cnt = count($judges);
	$d=($judges[0] ?? false);
	$s['action']="start";
	httpsocket($d['address'],$s);
    if($s['state'] == "successful")
        提示("启动评测机 {$_GET['grid']} 成功！", 取路径("submit/graderlist.php"));
    else
        异常("启动评测机 {$_GET['grid']} 失败！", 取路径("submit/graderlist.php"));
}

if (($_POST['action'] ?? $_GET['action'] ?? '')=='stop')
{
	$judges = Judge::address($_GET['grid']);
	$cnt = count($judges);
	$d=($judges[0] ?? false);
	$s['action']="shutdown";
	httpsocket($d['address'],$s);
    if($s['state'] == "successful")
        提示("关闭评测机 {$_GET['grid']} 成功！", 取路径("submit/graderlist.php"));
    else
        异常("关闭评测机 {$_GET['grid']} 失败！", 取路径("submit/graderlist.php"));
}
?>
