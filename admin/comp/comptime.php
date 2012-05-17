<?php
require_once("../../include/stdhead.php");
gethead(1,"修改比赛","比赛场次管理");
?>

<a href="../settings.php?settings=comp">比赛基本管理</a>
<?php
$p=new DataAccess();
$sql="select comptime.*,compbase.cname,groups.* from comptime,compbase,groups where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]} and groups.gid=comptime.group";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$d=$p->rtnrlt(0);
?>
<table width="100%" border="1">
  <tr>
    <td width="15%" scope="col">CTID</td>
    <td width="35%" scope="col"><?php echo $d[ctid] ?></td>
    <td width="15%">关联比赛</td>
    <td width="35%"><?php echo $d[cname] ?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[starttime]) ?></td>
    <td>结束时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[endtime]) ?></td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><a href="../user/index.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
    <td>公布成绩</td>
    <td><?php echo $d[showscore]?"是":"否" ?></td>
  </tr>
  <tr>
    <td>比赛状态</td>
    <td><?php
	 if (time()>$d[endtime]) echo "已结束"; else
	 if (time()<$d[endtime] && time()>$d[starttime]) echo "正在进行"; else
	 echo "还未开始"; 
	 ?></td>
    <td>阅读权限</td>
    <td><?php echo $d[readforce] ?></td>
  </tr>
  <tr>
    <td>场次介绍</td>
    <td><?php echo nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
    <td>修改场次信息</td>
    <td><a class="adminButton" href="editcomptime.php?action=edit&ctid=<?php echo $d[ctid] ?>">修改</a></td>
  </tr>
  <tr>
    <td>查看成绩</td>
    <td><a href="../../competition/report.php?ctid=<?php echo $d['ctid'] ?>" target="_blank">查看</a></td>
    <td>发布成绩</td>
    <td><a href="release.php?ctid=<?php echo $d['ctid'] ?>">发布</a></td>
  </tr>
</table>
    <?php
}
else
{
	echo '<script>document.location="../../error.php?id=19"</script>';
	exit;
}
?>
<?php
$sql="select compscore.uid,userinfo.realname,userinfo.nickname from compscore,userinfo where userinfo.uid=compscore.uid and compscore.ctid={$_GET[ctid]} order by uid asc";
$cnt=$p->dosql($sql);
if ($cnt)
{
?>
<form id="form1" name="form1" method="post" action="../../competition/judge.php">
<p>
  <input name="do" type="submit" id="do" value="评测选定" />
  <input name="do" type="submit" id="do" value="评测全部" />
  <input name="ctid" type="hidden" id="ctid" value="<?php echo $_GET['ctid'] ?>" />
</p>
<table width="100%" border="1">
  <tr>
    <th scope="col">用户昵称</th>
    <th scope="col">真实姓名</th>
    <th scope="col">提交记录</th>
  </tr>
<?php
	$q=new DataAccess();
	$tu=0;
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		if ($d[uid]==$tu) continue;
		$tu=$d[uid];
?>
  <tr>
    <td><a target="_blank" href="../../user/detail.php?uid=<?php echo $d[uid] ?>"><?php echo $d[nickname] ?></a></td>
    <td><a target="_blank" href="../../user/detail.php?uid=<?php echo $d[uid] ?>"><?php echo $d[realname] ?></a></td>
    <td>
	
	<table width="100%" border="1" bordercolor=#000000  cellspacing=0 cellpadding=4>
	  <tr>
		<th scope="col" width="6%">选定</th>
		<th scope="col" width="6%">CSID</th>
		<th scope="col" width="10%">题目名</th>
		<th scope="col" width="8%">代码</th>
		<th scope="col" width="16%">提交时间</th>
		<th scope="col" width="4%">得分</th>
		<th scope="col" width="10%">测试点</th>
	  </tr>
	<?php
	$sql="select compscore.csid,compscore.pid,compscore.lang,compscore.subtime,compscore.score,compscore.result,problem.probname from compscore,problem where problem.pid=compscore.pid and compscore.uid={$d[uid]} and compscore.ctid={$_GET[ctid]}";
	$c=$q->dosql($sql);
	if ($c)
	{
		for ($j=0;$j<$c;$j++)
		{
			$e=$q->rtnrlt($j);
	?>
	  <tr>
		<td><input name="doit[]" type="checkbox" id="doit[]" value="<?php echo $e[csid] ?>" />
		  <input name="doall[]" type="hidden" id="doall[]" value="<?php echo $e[csid] ?>" /></td>
		<td><?php echo $e[csid] ?></td>
		<td><a target="_blank" href="../../problem/problem.php?pid=<?php echo $e[pid] ?>"><?php echo $e[probname] ?></a></td>
		<td><a href="../../competition/code.php?csid=<?php echo $e[csid] ?>" target="_blank"><?php echo $STR[lang][$e[lang]] ?></a></td>
		<td><?php echo date("Y年m月d日 H:i:s",$e[subtime]) ?></td>
		<td><?php echo $e[score] ?></td>
		<td><?php echo 评测结果($e[result]) ?></td>
	  </tr>
	<?php
		}
	}
	?>
	</table>
</td>
<?php
	}
?>
  </tr>
</table>
</form>
<?php
}
?>

<?php
	include_once("../../include/stdtail.php");
?>
