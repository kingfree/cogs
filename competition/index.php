<?php
require_once("../include/stdhead.php");
gethead(1,"","比赛");
?>

现在时间：
<?php
if ($_GET['showold']==1)
	$showold=true;
else
	$showold=false;
echo date('Y-m-d H:i:s', time());
$p=new DataAccess();
$sql="select comptime.*,compbase.cname,compbase.contains,compbase.ouid,userinfo.nickname,groups.* from comptime,compbase,userinfo,groups where comptime.cbid=compbase.cbid and userinfo.uid=compbase.ouid and comptime.group=groups.gid order by starttime desc";
$cnt=$p->dosql($sql);
if ($cnt)
{
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		if (!$showold && time()-$d[endtime] >= 2592000) continue;
?>
	<div class="Comments" >
<table width="100%" border="1">
  <tr>
    <td width="15%">比赛名</td>
    <td width="85%"><?php echo $d[cname] ?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[starttime]) ?></td>
  </tr>
  <tr>
    <td>结束时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[endtime]) ?></td>
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
    <td>成绩表</td>
    <td>
<?php
if ($d[showscore] || $_SESSION[admin])
	echo "<a href='report.php?ctid=$d[ctid]'>查看成绩表</a> ";
if (!$d[showscore])
	echo "未公布";
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
  <tr>
    <td>参加比赛</td>
    <td><a href='comp.php?ctid=<?php echo $d[ctid] ?>&uid=<?php echo $_SESSION['ID'] ?>'>进入比赛</a></td>
  </tr>
</table>
	</div>


<?php
	echo "<hr class='Spliter'/>";
	}
}
else
{
	echo "还没有比赛！";
}
?>
<p><a href="?showold=1">查看过去的比赛</a></p>
<?php
	include_once("../include/stdtail.php");
?>