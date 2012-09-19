<?php
require_once("../include/header.php");
gethead(8,"sess","");
过滤();
$p=new DataAccess();
if(!$_POST['pid'])
异常("没有选择题目！", 取路径("problem/index.php"));
if(!$_POST['cname'])
异常("没有填写分类名！", 取路径("problem/problem.php?pid={$_POST['pid']}"));

$sql="select * from category where cname like '%{$_POST['cname']}%' or memo like '%{$_POST['cname']}%' limit 1";
$cnt=$p->dosql($sql);
if(!$cnt) {
	$sql1="insert into category(cname,memo) values('{$_POST['cname']}','{$_POST['memo']}')";
	$p->dosql($sql1);
} else {
    $e=$p->rtnrlt(0);
    $st=trim("{$e['memo']} {$_POST['memo']}");
	$sql1="update category set memo='$st' where caid={$e['caid']}";
	$p->dosql($sql1);
    $sql2="select * from tag where pid={$_POST['pid']} and caid={$e['caid']}";
	$al=$p->dosql($sql2);
    if($al) {
        提示("添加题目 {$_POST['pid']} 分类 “{$e['caid']}. {$_POST['cname']}” 成功，虽然它之前就已经存在了！", 取路径("problem/problem.php?pid={$_POST['pid']}"));
        exit(0);
    }
}

$cnt=$p->dosql($sql);
if(!$cnt)
异常("添加分类失败！", 取路径("problem/problem.php?pid={$_POST['pid']}"));

$e=$p->rtnrlt(0);
$sql2="insert into tag(pid,caid) values({$_POST['pid']},{$e['caid']})";
$p->dosql($sql2);
提示("添加题目 {$_POST['pid']} 分类 “{$e['caid']}. {$_POST['cname']}” 成功！", 取路径("problem/problem.php?pid={$_POST['pid']}"));
?>
