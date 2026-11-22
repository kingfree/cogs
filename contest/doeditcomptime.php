<?php
require_once("../include/header.php");
gethead(8,"修改比赛","");
requirePost();

if (($_POST['action'] ?? $_GET['action'] ?? '')=='add')
{
	$starttime=mktime($_POST['st_h'],$_POST['st_i'],$_POST['st_s'],$_POST['st_m'],$_POST['st_d'],$_POST['st_y']);
	$endtime=mktime($_POST['et_h'],$_POST['et_i'],$_POST['et_s'],$_POST['et_m'],$_POST['et_d'],$_POST['et_y']);
	$sc=0;
	if ($_POST['showscore']) $sc=1;
	ContestRound::create($_POST['cbid'], $_POST['intro'], $starttime, $endtime, $sc, $_POST['group']);
    提示("添加比赛场次 {$_POST['cbid']} - {$_POST['intro']} 成功！",取路径("contest/compbase.php"));
}

if (($_POST['action'] ?? $_GET['action'] ?? '')=='edit')
{
	$starttime=mktime($_POST['st_h'],$_POST['st_i'],$_POST['st_s'],$_POST['st_m'],$_POST['st_d'],$_POST['st_y']);
	$endtime=mktime($_POST['et_h'],$_POST['et_i'],$_POST['et_s'],$_POST['et_m'],$_POST['et_d'],$_POST['et_y']);
	$sc=0;
	if ($_POST['showscore']) $sc=1;
	ContestRound::edit($_POST['cbid'], $_POST['intro'], $starttime, $endtime, $sc, $_POST['group'], ($_POST['ctid'] ?? $_GET['ctid'] ?? ''));
    提示("修改比赛场次 {$_POST['cbid']} - {$_POST['intro']} 成功！",取路径("contest/compbase.php"));
}
?>
