<?php
require_once("../include/stdhead.php");
gethead(0,"admin","");
$LIB->cls_compile();
$LIB->func_socket();

$p=new DataAccess();
$sql="select compscore.lang,compscore.uid,problem.filename,compscore.ctid,problem.pid,problem.datacnt,problem.timelimit,problem.memorylimit,problem.plugin from compscore,problem where compscore.pid=problem.pid and compscore.csid={$_POST['csid']}";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$d=$p->rtnrlt(0);
}
else
{
	exit;
}
$cdir="{$SET['dir_competition']}/{$d[ctid]}";

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

$Cp=new Compiler($info);

$free=$Cp->getgds();

if (!$free) //非空闲
{
	echo "No Grader!Please Wait and Retry.";
	exit;
}

$Cp->lock();

$Cp->getdir();

$csucc=$Cp->compile();

if (!$csucc) {
	echo "0!C";
	$Cp->s_detail="C";
} else {
	for ($P=1;$P<=$d[datacnt];$P++)
	{
		$Cp->run($P);
		$Cp->getresult();
	}
	$Cp->getscore();
	echo "{$Cp->s_score}!{$Cp->s_detail}";
}

$Cp->writedb_comp($_POST['csid']);
$Cp->unlock();
?>
