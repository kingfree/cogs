<?php
require_once("../include/header.php");
gethead(0,"查看比赛","");
requirePost();
require_once __DIR__.'/../include/socket.php';

$entries = ContestEntry::forJudging($_POST['csid']);
$cnt = count($entries);
if ($cnt) {
	$d=($entries[0] ?? false);
} else {
	exit;
}
$cdir="{$SET['dir_competition']}/{$d['ctid']}";

$info=array();
$info['pid']=$d['pid'];
$info['uid']=$d['uid'];
$info['language']=$d['lang'];
$info['pname']=$d['filename'];
$info['datacnt']=$d['datacnt'];
$info['timelimit']=$d['timelimit'];
$info['memorylimit']=$d['memorylimit'];
$info['plugin']=$d['plugin'];
$info['compiledir']=$cdir;

$Cp=new Judging($info);

$free=$Cp->acquire($_POST['judger']);

if (!$free) //非空闲
{
	echo "No Grader!Please Wait and Retry.";
	exit;
}



$Cp->prepareSource();

$csucc=$Cp->compile();

if (!$csucc) {
	echo "0!C";
	$Cp->s_detail="C";
} else {
	for ($P=1;$P<=$d['datacnt'];$P++)
	{
		$Cp->run($P);
		$Cp->getresult();
	}
	$Cp->getscore();
	echo "{$Cp->s_score}!{$Cp->s_detail}!".$Cp->gettotaltime()."!".$Cp->getmemory();
}

$Cp->writedb_comp($_POST['csid']);
$Cp->unlock();
?>
