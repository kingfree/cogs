<?php
require_once("../include/stdhead.php");
gethead(1,"sess","题目详细信息");
?>

<?php
$p=new DataAccess();
$sql="select * from problem where pid={$_GET[pid]}";
$cnt=$p->dosql($sql);
if ($cnt)
{
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
		if (time()>$e[endtime])
		{
			echo '<script>document.location="../error.php?id=20"</script>';
			exit;
		}
		else
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
		if ($d[submitable] || !$pb)
		{
			echo '<script>document.location="../error.php?id=18"</script>';
			exit;
		}
	}
}
else
{
	echo '<script>document.location="../error.php?id=11"</script>';
	exit;
}
?>

<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td width="15%">题目名</td>
    <td width="35%"><b><?php echo $d[probname]; ?></b></td>
    <td width="15%">所属比赛</td>
    <td width="35%"><?php echo $e['cname']." ".$e['intro']; ?></td>
  </tr>
  <tr>
    <td>输入文件名</td>
    <td><?php echo $d[filename]; ?>.in</td>
    <td>输出文件名</td>
    <td><?php echo $d[filename]; ?>.out</td>
  </tr>
  <tr>
    <td>时间限制</td>
    <td><?php echo $d['timelimit']; ?> ms </td>
    <td>内存限制</td>
    <td><?php echo $d['memorylimit']; ?> MB </td>
  </tr>
  <tr>
    <td>比赛开始时间</td>
    <td><?php echo date("Y-m-d H:i:s",$e[starttime]) ?></td>
    <td>比赛结束时间</td>
    <td><?php echo date("Y-m-d H:i:s",$e[endtime]) ?></td>
  </tr>
</table>
<h3>题目内容</h3>
<div class="MainText">
  <?php echo $d[detail] ?></div>
<hr class="Spliter" />
<form action="submit.php" method="post" enctype="multipart/form-data" name="sub">
代码：
<input type="file" name="file" class="Button"/>
<input type="radio" name="lang" id="pas" value="pas" /><label for="pas">Pascal</label>
<input type="radio" name="lang" id="c" value="c" /><label for="c">C</label>
<input type="radio" name="lang" id="cpp" value="cpp" checked=1/><label for="cpp">C++</label>
<input type="submit" name="Submit" value="提交"/>
<input name="filename" type="hidden" id="filename" value="<?php echo $d[filename]; ?>" />
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']; ?>" />
<input name="ctid" type="hidden" id="pid" value="<?=$_GET[ctid]; ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="102400">
</form>
<!--  <p>提交代码  </p>
  <p>
    源代码
      <input type="file" name="file" class="Button"/>
      语言
      <select name="lang" id="lang"  class="InputBox">
      <option value="pas">Pascal</option>
      <option value="c">C</option>
      <option value="cpp">C++</option>
    </select>
  </p>
  <p>  
    <input type="submit" name="Submit" value="提交"  class="Button"/>
    <input name="filename" type="hidden" id="filename" value="<?php echo $d[filename]; ?>" />
	<input name="pid" type="hidden" id="pid" value="<?php echo $d[pid]; ?>" />
	<input name="ctid" type="hidden" id="pid" value="<?php echo $_GET[ctid]; ?>" />
  </p>
</form>-->

<?php
	include_once("../include/stdtail.php");
?>
