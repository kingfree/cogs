<?php
require_once("../include/header.php");
gethead(8,"sess","");

$p=new DataAccess();
if(!$_POST['pid'])
异常("没有选择题目！", 取路径("problem/index.php"));
if(!$_POST['cname'])
异常("没有填写分类名！", 取路径("problem/problem.php?pid={$_POST['pid']}"));

$sql = new SqlQuery("select * from category where cname like ? or memo like ? limit 1", ['%'. $_POST['cname'] .'%', '%'. $_POST['cname'] .'%']);
$cnt=$p->dosql($sql);
if(!$cnt) {
	$sql1 = new SqlQuery("insert into category(cname,memo) values(?,?)", [$_POST['cname'], $_POST['memo']]);
	$p->dosql($sql1);
} else {
    $e=$p->rtnrlt(0);
    $st=trim("{$e['memo']} {$_POST['memo']}");
	$sql1 = new SqlQuery("update category set memo=? where caid=?", [$st, $e['caid']]);
	$p->dosql($sql1);
    $sql2 = new SqlQuery("select * from tag where pid=? and caid=?", [$_POST['pid'], $e['caid']]);
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
$sql2 = new SqlQuery("insert into tag(pid,caid) values(?,?)", [$_POST['pid'], $e['caid']]);
$p->dosql($sql2);
提示("添加题目 {$_POST['pid']} 分类 “{$e['caid']}. {$_POST['cname']}” 成功！", 取路径("problem/problem.php?pid={$_POST['pid']}"));
?>
