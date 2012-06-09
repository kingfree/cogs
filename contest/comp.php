<?php
require_once("../include/header.php");
gethead(0,"","");
$uid = (int) ($_GET['uid'] ? $_GET['uid'] : $_SESSION['ID']);
$p=new DataAccess();
$sql="select compbase.cname,compbase.contains,comptime.starttime,comptime.endtime,comptime.showscore,comptime.intro,groups.* from comptime,compbase,groups where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]} and comptime.group=groups.gid";
$cnt=$p->dosql($sql);
if ($cnt) {
	$d=$p->rtnrlt(0);
    $contains=$d['contains'];
} else 异常("未查询到记录！");
if (time()<$d['starttime'] && !有此权限('查看比赛')) 
    异常("比赛还未开始，题目暂不公布。");
else {
	$pbs=explode(":",$contains);
	foreach($pbs as $k=>$v) {
		$sql="select probname from problem where pid={$v}";
		$p->dosql($sql);
		$d=$p->rtnrlt(0);
		$pname=$d[probname];
		echo "<script>document.location='problem.php?pid={$v}&ctid={$_GET['ctid']}&uid={$uid}' </script>";
        exit;
	}
}
?>
