<?php
require_once("../include/stdhead.php");
gethead(1,"sess","题目信息");

$uid = $_GET['uid'];
?>

<?php
$p=new DataAccess();
$sql="select * from problem where pid={$_GET[pid]}";
$cnt=$p->dosql($sql);
if ($cnt) {
	$d=$p->rtnrlt(0);
	$q=new DataAccess();
	$sql="select cname,starttime,endtime,contains,intro,groups.* from compbase,comptime,groups where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]} and comptime.group=groups.gid";
	$c=$q->dosql($sql);
	$e=$q->rtnrlt(0);
	
	$subgroup=$LIB->getsubgroup($q,$e['gid']);
	$subgroup[0]=$e['gid'];
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

	if (!$c)
	{
		echo '<script>document.location="../error.php?id=19"</script>';
		exit;
	}
	$pbs=explode(":",$e['contains']);
	$pb=0;
	foreach($pbs as $k=>$v)
	{
		if ($v==$_GET['pid'])
			$pb=1;
	}
	if (!$_SESSION['admin']>0)
	{
		/*if (time()>$e[endtime])
		{
			echo '<script>document.location="../error.php?id=20"</script>';
			exit;
		}
		else*/
		if (time()<$e[starttime])
		{
			echo '<script>document.location="../error.php?id=21"</script>'; 
			exit;
		}
		
		if ($d[readforce]>$_SESSION[readforce])
		{
			echo '<script>document.location="../error.php?id=17"</script>';
			exit;
		}
		/*if ($d[submitable] || !$pb)
		{
			echo '<script>document.location="../error.php?id=18"</script>';
			exit;
		}*/
        if($_SESSION['ID'] != $_GET['uid'] && time()<$e[endtime])
            $uid = $_SESSION['ID'];
	}
}
else
{
	echo '<script>document.location="../error.php?id=11"</script>';
	exit;
}
?>

<div id='problem'>
<table class="pdetail" width=100% border=0><tr>
<td width=250px valign=top>
<table width="100%" style="margin: 2px 0;">
  <tr>
    <td width=60px>当前比赛</td>
    <td><b><?php echo $e['cname']; ?></b></td>
  </tr>
  <tr>
    <td>比赛状态</td>
    <td><?php
	 if (time()>$e[endtime]) echo "已结束"; else
	 if (time()<$e[endtime] && time()>$e[starttime]) echo "正在进行"; else
	 echo "还未开始"; 
	 ?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date("Y-m-d H:i:s",$e[starttime]) ?></td>
  </tr>
  <tr>
    <td>结束时间</td>
    <td><?php echo date("Y-m-d H:i:s",$e[endtime]) ?></td>
  </tr>
  <tr>
    <td>场次介绍</td>
    <td valign=top><?php echo nl2br(sp2n(htmlspecialchars($e[intro]))) ?></td>
  </tr>
</table>
<div width="80%" border=1>
<ol>
  <?
	$r=new DataAccess();
    $contains=$e['contains'];
	$pbs=explode(":",$contains);
    $ppid = 1;
	foreach($pbs as $k=>$v) {
		$sql="select probname from problem where pid={$v}";
		$r->dosql($sql);
		$f=$r->rtnrlt(0);
		$pname=$f[probname];
		$sql="select * from compscore where uid='{$uid}' and compscore.pid={$v} and compscore.ctid={$_GET[ctid]}";
		$cnt=$r->dosql($sql);
		if($cnt) $f=$r->rtnrlt(0);
  ?>
    <li style="background:<?=($v == $_GET['pid'])?"white":"lightblue"?>;
    font-size:20px; display:block;">
    <a href="cdetail.php?pid=<?=$v?>&ctid=<?=$_GET[ctid]?>&uid=<?=$uid?>">
    <? echo $ppid . ". "; $ppid++; echo $pname; ?>
    </a>
    </li>
<?php 
	}
?>
</ol>
</div>
<table width="100%" style="margin: 2px 0;">
  <tr>
    <td width=60px>题目名称</td>
    <td><b><?php echo $d[probname]; ?></b></td>
  </tr>
<tr>
<td>题目文件</td>
<td><?php echo $d[filename]; ?>.cpp/pas/c</td>
</tr>
<tr>
<td>输入输出</td>
<td><?php echo $d[filename]; ?>.in/out</td>
</tr>
  <tr>
    <td>时间限制</td>
<td><?php echo $d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td>
  </tr>
  <tr>
    <td>内存限制</td>
    <td><?php echo $d['memorylimit']; ?> MiB </td>
  </tr>
  <tr>
  <td colspan=2 align=right>
  <? if((time() < $e['endtime'] && time() > $e['starttime']) || $_SESSION['admin']) { ?>
<form action="submit.php" method="post" enctype="multipart/form-data" name="sub">
<input type="file" name="file" class="Button"/>
<input type="radio" name="lang" id="pas" value="pas" /><label for="pas">Pascal</label>
<input type="radio" name="lang" id="c" value="c" /><label for="c">C</label>
<input type="radio" name="lang" id="cpp" value="cpp" checked=1/><label for="cpp">C++</label>
<input type="submit" name="Submit" value="提交"/>
<input name="filename" type="hidden" id="filename" value="<?php echo $d[filename]; ?>" />
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']?>" />
<input name="ctid" type="hidden" id="pid" value="<?=$_GET['ctid']?>" />
<input name="endtime" type="hidden" id="endtime" value="<?=$e['endtime']?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="102400">
</form>
<? } ?>
  </td>
  </tr>
</table>
<table width="100%" style="margin: 2px 0;" border=1>
<? $v = $_GET['pid'];
$sql="select * from compscore,userinfo where compscore.uid='{$uid}' and userinfo.uid='{$uid}' and compscore.pid={$v} and compscore.ctid={$_GET[ctid]}";
$cnt=$r->dosql($sql);
$f=$r->rtnrlt(0);
?>
<tr>
<td width=60px><b><?php echo $f['realname']; ?></b></td>
<td><a href="../user/detail.php?uid=<?=$uid?>"><?php echo $f['nickname']; ?></a></td>
</tr>
<tr>
<td>提交时间</td>
<td><?php if($f[subtime]) echo date('Y-m-d H:i:s',$f['subtime']); else echo "未提交"; ?></td>
</tr>
<tr>
<td>得分</td>
<td><?php echo $f['score']; ?></td>
</tr>
<tr>
<td>评测结果</td>
<td><?php echo judgeresult($f['result']); ?></td>
</tr>
<tr>
<td>代码</td>
<td><a href="code.php?csid=<?php echo $f[csid] ?>" target="_blank">
<?php if($f['subtime']) echo $STR[lang][$f[lang]] ?>
</a></td>
</tr>
</table>

</div>
</td>
<td class="MainText" valign=top>
<?php echo $d[detail] ?>
</td>
</tr></table>
</div>
<?php
	include_once("../include/stdtail.php");
?>
