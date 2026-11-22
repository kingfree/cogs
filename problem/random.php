<?php
require_once("../include/header.php");
$p = new DataAccess();
$sql = new SqlQuery("select max(pid) as maxpid from problem");
$cnt = $p->dosql($sql);
$d=$p->rtnrlt(0);
$mpid = $d['maxpid'];

function canuse($pid) {
	$p = new DataAccess();
	$sql = new SqlQuery("select pid from problem where pid = ? and submitable = 1", [$pid]);
	$cnt = $p->dosql($sql);
	if($cnt == 0) return false;
	if ($_SESSION['ID']) {
		$sql = new SqlQuery("SELECT * FROM submit WHERE pid = ? AND uid =? order by accepted desc limit 1", [$pid, $_SESSION['ID']]);
		$ac=$p->dosql($sql);
		if ($ac) {
			$e=$p->rtnrlt(0);
			if ($e['accepted']) return false;
			else true;
		}
	}
	return true;
}

$ok = 0;
for($i=0; $i<$mpid; $i++) {
	$pid = rand(1, $mpid);
	if(canuse($pid)) {
		$url = "problem.php?pid=" . $pid;
		header("Location:$url");
        $ok = 1;
		break;
	}
}
if(!$ok) {
	$pid = rand(1, $mpid);
    $url = "problem.php?pid=" . $pid;
    header("Location:$url");
}
?>
