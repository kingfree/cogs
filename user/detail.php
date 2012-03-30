<?php
require_once("../include/stdhead.php");
gethead(1,"","用户详细信息");
?>

<?php
$p=new DataAccess();
$sql="select userinfo.*,groups.gname,groups.gid from userinfo,groups where userinfo.uid={$_GET['uid']} and userinfo.gbelong=groups.gid";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$d=$p->rtnrlt(0);
?>
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
<td width="10%" scope="col">用户ID</td>
<td width="60%" scope="col"><?php echo $d['uid'] ?></td>
<th> 头像 </th>
  </tr>
<tr>
<td>用户名</td>
<td><?php echo $d['usr'] ?></td>
<th rowspan=9 valign=top>
<?=gravatar::showImage($d['email'], 200);?>
</th>
</tr>
  <tr>
    <td>昵称</td>
    <td><?php echo $d['nickname'] ?></td>
  </tr>
  <tr>
    <td>E-mail</td>
    <td><?php echo $d['email'] ?></td>
  </tr>
  <tr>
    <td>阅读权限</td>
    <td><?php echo $d['readforce'] ?></td>
  </tr>
  <tr>
    <td>管理权限</td>
    <td><?php echo $STR[adminn][$d['admin']] ?></td>
  </tr>
  <tr>
    <td>所属分组</td>
    <td><a href="../information/userlist.php?gid=<?php echo $d['gid'] ?>"><?php echo $d['gname'] ?></a></td>
  </tr>
  <tr>
    <td>等级</td>
    <td><?php echo $d['grade'] ?></td>
  </tr>
  <tr>
    <td>注册时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d['regtime']) ?></td>
  </tr>
  <tr>
    <td>个人介绍</td>
    <td><?php echo nl2br(sp2n(htmlspecialchars($d['memo']))) ?></td>
  </tr>
  <?php if ($_SESSION['admin']>0 || $_SESSION['ID']==$d['uid']){ ?>
  <tr bgcolor="#99FFCC">
    <td>真实姓名</td>
    <td><?php echo $d['realname'] ?></td>
  </tr>
  <tr bgcolor="#99FFCC">
    <td>登录IP</td>
    <td><a href="../addons/ipquery/?ip=<?php echo $d['lastip'] ?>" target="_blank"><?php echo $d['lastip'] ?></a></td>
  </tr>
  <?php } ?>
</table>
<?php
}
else
{
	echo '<script>document.location="../error.php?id=11"</script>';
	exit;
}
?>
<p><a href="../information/submitlist.php?uid=<?php echo $_GET['uid']?>" target="_blank">查看全部提交记录</a></p>
<?php
$accnt=0;
$sql="select problem.pid,problem.probname,submit.accepted,submit.sid from submit,problem where submit.uid={$_GET['uid']} and submit.pid=problem.pid order by problem.pid asc, submit.accepted desc ";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$table_width=8;
?>

<table border="1" bordercolor=#000000  cellspacing=0 cellpadding=4>
	<tr>
<?php
	$last=0;
	$linecnt=0;
	$line=1;
    $ppp=array();
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		if($last==$d['pid']) continue;
		if($d['accepted']) $accnt++;
		$last=$d['pid'];
        if($ppp[$d['pid']]) continue;
        $ppp[$d['pid']] = true;
		$linecnt++;
?>
<td><a href="../problem/submitdetail.php?id=<?php echo $d['sid'] ?>" target="_blank"><img src='../images/sign/<?=$d['accepted']?"right":"error"?>.gif' border=0 /></a><a href="../problem/pdetail.php?pid=<?php echo $d['pid'] ?>" target="_blank"><?php echo $d['probname'] ?></a></td>
<?php
		if ($linecnt==$table_width)
		{
			$linecnt=0;
			$line++;
?>
	</tr>
	<tr>
<?php
		}
	}
if ($linecnt>0 && $line>1)
{
	for ($i=$linecnt;$i<$table_width;$i++)
	{
?>
		<td>&nbsp;</td>
<?php
	}
}
?>
	</tr>
</table>
<?php
}
?>
<p>通过了<strong><?php echo $accnt ?></strong>道题，一共提交了<strong><?php echo $cnt ?></strong>次，通过率为<strong><?php printf("%.2lf",$cnt==0?0:$accnt / $cnt * 100) ?>%</strong>。</p>
<p>参加过的比赛</p>
<?php
$sql="select compbase.cbid,compbase.cname,compscore.subtime,comptime.ctid from compscore,comptime,compbase where compscore.uid={$_GET['uid']} and comptime.ctid=compscore.ctid and comptime.cbid=compbase.cbid order by comptime.endtime desc";
$cnt=$p->dosql($sql);
if ($cnt)
{
?>

<table border="1" bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th scope="col">比赛名</th>
    <th scope="col">参加时间</th>
    <th scope="col">详细信息</th>
  </tr>
<?php
	$last=0;
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		if ($last==$d['cbid']) continue;
		$last=$d['cbid'];
?>
  <tr>
    <td><?php echo $d['cname'] ?></td>
    <td><?php echo date("Y-m-d",$d['subtime']) ?></td>
    <td><a href="../competition/comp.php?ctid=<?php echo $d['ctid'] ?>&uid=<?php echo $_GET['uid'] ?>" target="_blank">查看</a></td>
  </tr>
<?php
	}
?>
</table>
<?php
}
?>

<?php
	include_once("../include/stdtail.php");
?>
