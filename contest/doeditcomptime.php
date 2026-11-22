<?php
require_once("../include/header.php");
gethead(8,"修改比赛","");

if ($_REQUEST['action']=='add')
{
	$p=new DataAccess();
	$starttime=mktime($_POST['st_h'],$_POST['st_i'],$_POST['st_s'],$_POST['st_m'],$_POST['st_d'],$_POST['st_y']);
	$endtime=mktime($_POST['et_h'],$_POST['et_i'],$_POST['et_s'],$_POST['et_m'],$_POST['et_d'],$_POST['et_y']);
	$sc=0;
	if ($_POST['showscore']) $sc=1;
	$sql = new SqlQuery("insert into comptime(cbid,intro,starttime,endtime,showscore,`group`) values(?,?,?,?,?,?)", [$_POST['cbid'], $_POST['intro'], $starttime, $endtime, $sc, $_POST['group']]);
	$p->dosql($sql);
    提示("添加比赛场次 {$_POST['cbid']} - {$_POST['intro']} 成功！",取路径("contest/compbase.php"));
}

if ($_REQUEST['action']=='edit')
{
	$p=new DataAccess();
	$starttime=mktime($_POST['st_h'],$_POST['st_i'],$_POST['st_s'],$_POST['st_m'],$_POST['st_d'],$_POST['st_y']);
	$endtime=mktime($_POST['et_h'],$_POST['et_i'],$_POST['et_s'],$_POST['et_m'],$_POST['et_d'],$_POST['et_y']);
	$sc=0;
	if ($_POST['showscore']) $sc=1;
	$sql = new SqlQuery("update comptime set cbid=?,intro=?,starttime=?,endtime=?,showscore=?,`group`=? where ctid=?", [$_POST['cbid'], $_POST['intro'], $starttime, $endtime, $sc, $_POST['group'], $_REQUEST['ctid']]);
	$p->dosql($sql);
    提示("修改比赛场次 {$_POST['cbid']} - {$_POST['intro']} 成功！",取路径("contest/compbase.php"));
}
?>
