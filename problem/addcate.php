<?php
require_once("../include/header.php");
gethead(8,"sess","");
requirePost();

if (!Problem::forReader((int)($_POST['pid'] ?? 0))) { http_response_code(404); exit('题目不存在或未开放。'); }
if(!$_POST['cname'])
异常("没有填写分类名！", 取路径("problem/problem.php?pid={$_POST['pid']}"));

$categories = Category::matching('%'. $_POST['cname'] .'%', '%'. $_POST['cname'] .'%');
$cnt = count($categories);
if(!$cnt) {
	Category::create($_POST['cname'], $_POST['memo']);
} else {
    $e=($categories[0] ?? false);
    $st=trim("{$e['memo']} {$_POST['memo']}");
	Category::updateMemo($st, $e['caid']);
    $categories = Category::hasProblem($_POST['pid'], $e['caid']);
	$al = count($categories);
    if($al) {
        提示("添加题目 {$_POST['pid']} 分类 “{$e['caid']}. {$_POST['cname']}” 成功，虽然它之前就已经存在了！", 取路径("problem/problem.php?pid={$_POST['pid']}"));
        exit(0);
    }
}

$categories = Category::matching('%'.$_POST['cname'].'%', '%'.$_POST['cname'].'%');
$cnt = count($categories);
if(!$cnt)
异常("添加分类失败！", 取路径("problem/problem.php?pid={$_POST['pid']}"));

$e=($categories[0] ?? false);
Category::attach($_POST['pid'], $e['caid']);
提示("添加题目 {$_POST['pid']} 分类 “{$e['caid']}. {$_POST['cname']}” 成功！", 取路径("problem/problem.php?pid={$_POST['pid']}"));
?>
