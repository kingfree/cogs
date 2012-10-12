<?php
require_once("../include/header.php");
gethead(8,"sess","");
过滤();
$p=new DataAccess();
if(!$_POST['pid'])
异常("没有选择题目！", 取路径("problem/index.php"));
if(!$_POST['title'])
异常("没有填写题解名称！", 取路径("problem/problem.php?pid={$_POST['pid']}"));

$sql="select * from solution where `link`='' limit 1";
$cnt=$p->dosql($sql);
if(!$cnt) {
	$sql1="insert into category(cname,memo) values('{$_POST['cname']}','{$_POST['memo']}')";
	$p->dosql($sql1);
} else {
    提示("添加题目 {$_POST['pid']} 分类 “{$e['caid']}. {$_POST['cname']}” 成功，虽然它之前就已经存在了！", 取路径("problem/problem.php?pid={$_POST['pid']}"));
}

?>
