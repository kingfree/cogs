<?php
require_once("../include/header.php");
gethead(8,"修改比赛","");
requirePost();

if (($_POST['action'] ?? $_GET['action'] ?? '')=='add')
{
	@$cons=implode(":",$_POST['cons']);
	Contest::create($_POST['cname'], $cons, $_SESSION['ID']);
    提示("添加比赛 {$_POST['cname']} 成功！",取路径("contest/compbase.php"));
}

if (($_POST['action'] ?? $_GET['action'] ?? '')=='edit')
{
	@$cons=implode(":",$_POST['cons']);
	Contest::edit($_POST['cname'], $cons, ($_POST['cbid'] ?? $_GET['cbid'] ?? ''));
    提示("修改比赛 {$_POST['cname']} 成功！",取路径("contest/compbase.php"));
}
?>
