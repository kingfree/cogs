<?php
require_once("../include/stdhead.php");
gethead(1,"sess","比赛详细信息");
?>

<?php
$p=new DataAccess();
$sql="select compbase.cname,compbase.contains,comptime.starttime,comptime.endtime,comptime.showscore,comptime.intro,groups.* from comptime,compbase,groups where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]} and comptime.group=groups.gid";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$d=$p->rtnrlt(0);
	if (time()<$d['endtime'] && !($_SESSION['admin']>0) && $_GET['uid']!=$_SESSION['ID'])
		exit;
	$subgroup=$LIB->getsubgroup($p,$d['gid']);
	$subgroup[0]=$d['gid'];
	$promise=false;
	foreach($subgroup as $value)
	{
		if ($value==(int)$_SESSION['group'])
		{
			$promise=true;
			break;
		}
	}
	if (!$promise && !($_SESSION['admin']>0))
		exit;
?>

<a href=".">返回比赛列表</a>
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td width="15%" scope="col">比赛名</td>
    <td width="85%" scope="col"><?php echo $d['cname'] ?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d['starttime']) ?></td>
  </tr>
  <tr>
    <td>结束时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d['endtime']) ?></td>
  </tr>
  <tr>
    <td>比赛状态</td>
    <td><?php
	 if (time()>$d[endtime]) echo "已结束"; else
	 if (time()<$d[endtime] && time()>$d[starttime]) echo "正在进行"; else
	 echo "还未开始"; 
	 ?></td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><a href="../information/userlist.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
  </tr>
  <tr>
    <td>场次介绍</td>
    <td><?php echo nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
  </tr>
</table>
<?php
$contains=$d['contains'];
}
else
{
	echo '<script>document.location="../error.php?id=19"</script>';
	exit;
}
$self=true;
if ($_GET['uid']!=$_SESSION['ID'])
{
	$self=false;
	$sql="select nickname from userinfo where uid={$_GET['uid']}";
	$p->dosql($sql);
	$d=$p->rtnrlt(0);
?>
<p>以下为用户的<a href="../user/detail.php?uid=<?php echo $_GET['uid'] ?>"><?php echo $d['nickname'] ?></a>参赛状态</p>
<?
}
if (time()<$d['starttime'] && !($_SESSION['admin']>0))
{
?>
<h1>比赛还未开始，题目暂不公布。</h1>
<? } else {
    ?>
  <p><table border="1" bordercolor=#000000 cellspacing=0 cellpadding=4>
  <tr>
<?php if ($self) { ?>
    <td scope="col">进入提交</td>
<?php } ?>
    <td scope="col">题目名</td>
    <td scope="col">提交时间</td>
    <td scope="col">代码</td>
    <td scope="col">成绩</td>
    <td scope="col">测试点</td>
  </tr>
<?php
	$pbs=explode(":",$contains);
	foreach($pbs as $k=>$v)
	{
		$sql="select probname from problem where pid={$v}";
		$p->dosql($sql);
		$d=$p->rtnrlt(0);
		$pname=$d[probname];
		echo '<script>document.location="cdetail.php?pid='.$v.'&ctid='.$_GET[ctid].'&uid='.$_GET['uid'].'"</script>';
        exit;
		
		$sql="select * from compscore where uid='{$_GET['uid']}' and compscore.pid={$v} and compscore.ctid={$_GET[ctid]}";
		$cnt=$p->dosql($sql);
		if($cnt) $d=$p->rtnrlt(0);
?>
  <tr>
    <?php if ($self) { ?><td><a href="cdetail.php?pid=<?php echo $v ?>&ctid=<?php echo $_GET[ctid] ?>" target="_blank">提交</a></td><?php } ?>
    <td><?php echo $pname ?></td>
    <td><?php if($d[subtime]) echo date('Y-m-d H:i:s',$d['subtime']); else echo "未提交"; ?></td>
    <td><a href="code.php?csid=<?php echo $d[csid] ?>" target="_blank">
      <?php if($d['subtime']) echo $STR[lang][$d[lang]] ?>
    </a></td>
    <td><?php echo $d['score']; ?></td>
    <td><?php echo judgeresult($d['result']); ?></td>
  </tr>
<?php 
	}
?>
</table>
  </p>
<?php } ?>

<?php
	include_once("../include/stdtail.php");
?>
