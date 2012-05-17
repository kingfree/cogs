<?php
require_once("../include/stdhead.php");
$p = new DataAccess();
$sql = "select max(pid) as maxpid from problem";
$cnt = $p->dosql($sql);
$d=$p->rtnrlt(0);
$mpid = $d['maxpid'];

function canuse($pid) {
	$p = new DataAccess();
	$sql = "select pid from problem where pid = $pid and submitable = 1";
	$cnt = $p->dosql($sql);
	if($cnt == 0) return false;
	if ($_SESSION['ID']) {
		$sql="SELECT * FROM submit WHERE pid = $pid AND uid ={$_SESSION['ID']} order by accepted desc limit 1";
		$ac=$p->dosql($sql);
		if ($ac) {
			$e=$p->rtnrlt(0);
			if ($e['accepted']) return false;
			else true;
		}
	}
	return true;
}

for(;;) {
	$pid = rand(1, $mpid);
	if(canuse($pid)) {
		$url = "problem.php?pid=" . $pid;
		header("Location:$url");
		break;
	}
}
?>
