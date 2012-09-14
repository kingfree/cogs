<?php
require_once("../include/header.php");
gethead(8,"修改页面","");
过滤();
//date_default_timezone_set("Asia/Shanghai");
$_POST['text'] = str_replace("'", "\'", $_POST['text']);
if ($_REQUEST[action]=='add') {
	$p=new DataAccess();
	$sql="insert into page(title,`force`,`text`,`time`,etime,uid,`group`) values('{$_POST[title]}','{$_POST[force]}','".($_POST[text])."','".time()."','".time()."','{$_SESSION[ID]}','{$_POST['group']}')";
	$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$aid=$d['aid'];
    提示("新建页面 {$_POST[title]} 成功",取路径("page/page.php?aid=$aid"));
} else if ($_REQUEST[action]=='edit') {
	$p=new DataAccess();
	$sql="update page set title='{$_POST[title]}',`force`={$_POST[force]}, etime=".time().",`text`='".($_POST[text])."',`group`='{$_POST['group']}' where aid={$_REQUEST[aid]}";
	$p->dosql($sql);
	$aid=$_REQUEST[aid];
    提示("修改页面 {$_POST[title]} 成功",取路径("page/page.php?aid=$aid"));
} else if ($_REQUEST[action]=='del') {
	$p=new DataAccess();
	$sql="delete from page where aid={$_REQUEST[aid]}";
	$p->dosql($sql);
	$aid=0;
    异常("删除页面 {$_REQUEST[aid]} 成功",取路径("page/index.php"));
}
?>

