<?php
require_once("../include/stdhead.php");
gethead(1,"","比赛");
?>

<?php if ($_SESSION['admin']>0){ ?>
<a class="adminButton" href="../admin/comp/editcompbase.php?action=add">添加新比赛</a>
<?php } ?>
<center>
现在时间：
<?php
if ($_GET['showold']==1)
	$showold=true;
else
	$showold=false;
echo date('Y-m-d H:i:s', time());
$p=new DataAccess();
$sql="select comptime.*,compbase.*,userinfo.nickname,groups.* from comptime,compbase,userinfo,groups where comptime.cbid=compbase.cbid and userinfo.uid=compbase.ouid and comptime.group=groups.gid order by starttime desc";
$cnt=$p->dosql($sql);
if ($cnt)
{
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		if (!$showold && time()-$d[endtime] >= 2592000) continue;
?>
<br />
<table border=0>
<tr>
<td>
<table border="1">
  <tr>
    <td width=80px>比赛名</td>
    <td><b><?php echo $d[cname] ?></b></td>
    <td width=80px>比赛状态</td>
    <td><?php
	 if (time()>$d[endtime]) echo "已结束"; else
	 if (time()<$d[endtime] && time()>$d[starttime]) echo "<span style='color: red'>正在进行</span>"; else
	 echo "<span style='color: blue'>还未开始</span>"; 
	 ?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[starttime]) ?></td>
    <td>结束时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[endtime]) ?></td>
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
    <td>开放分组</td>
    <td><a href="../information/userlist.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
  </tr>
  <tr>
    <td>参加比赛</td>
    <td><b><a href='comp.php?ctid=<?php echo $d[ctid] ?>&uid=<?php echo $_SESSION['ID'] ?>'>进入比赛</a></b></td>
    <td>场次介绍</td>
    <td><?php echo nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
  </tr>
</table>
</td>
<td>
<? if($_SESSION['admin']) { ?>
<table class=admin border=1>
<tr><td width=80px>组织者</td>
<td width=120px><a href='../user/detail.php?uid=<?=$d['ouid']?>' target='_blank'><?=$d['nickname']?></a></td></tr>
<tr><td>阅读权限</td>
<td><?=$d['readforce']?></td></tr>
<tr><td>cbid</td>
<td><a href="../admin/comp/editcompbase.php?action=edit&cbid=<?=$d['cbid']?>">修改比赛<?=$d['cbid']?></a></td></tr>
<tr><td>ctid</td>
<td><a href="../admin/comp/editcomptime.php?action=edit&ctid=<?=$d['ctid']?>">编辑场次<?=$d['ctid']?></a></td></tr>
</table>
<? } ?>
</td>
</tr>
</table>

<?php
	}
}
else
{
	echo "<h1>还没有比赛！</h1>";
}
?>
<p><a href="?showold=1">查看过去的比赛</a></p>
</center>
<?php
	include_once("../include/stdtail.php");
?>
