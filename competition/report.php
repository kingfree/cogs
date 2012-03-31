<?php
require_once("../include/stdhead.php");
gethead(1,"sess","成绩公布");
?>

<?php
	$p=new DataAccess();
	$r=new DataAccess();
	$sql="select comptime.starttime,compbase.contains,comptime.showscore from compbase,comptime where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]}";
	$cnt=$p->dosql($sql);
	if (!$cnt)
	{
		exit;
	}
	$d=$p->rtnrlt(0);
	if (!$d[showscore] && !$_SESSION[admin])
	{
		echo "<h1>成绩还未公布</h1>";
	include_once("../include/stdtail.php");
		exit;
	}
if(time() < $d['starttime'] && !$_SESSION[admin]) {
    echo "<h1>比赛尚未开始，不能查看关于题目的任何信息！</h1>";
	include_once("../include/stdtail.php");
    exit;
}
	
	$q=new DataAccess();
	$pbs=explode(":",$d['contains']);
?>

<p><a href=".">返回比赛列表</a></p>
<table border="1" align=center>
  <tr>
    <th width="30px" scope="col"><a href="javascript:qsort('rank')">名次</a></th>
    <th width="40px" scope="col"><a href="javascript:qsort('uid')">UID</a></th>
    <th width="120px" scope="col">用户昵称</th>
	<?php if ($_SESSION['admin']>0) { ?>
    <th width="60px" scope="col">姓名</th>
	<?php } ?>
<?php
	$cnt_prob=0;
	foreach($pbs as $k=>$v)
	{
		$mbarray[$v][score]=0;
		$mbarray[$v][result]="N";
		$sql="select probname from problem where pid={$v}";
		$p->dosql($sql);
		$d=$p->rtnrlt(0);
		$cnt_prob++;
?>
    <th width="150px" scope="col"><?php echo $d[probname] ?></th>
    <th width="50px" scope="col"><a href="javascript:qsort('score<?php echo $v ?>_')">得分</a></th>
<?php
	}
?>
    <th width="55px" scope="col"><a href="javascript:qsort('sum')">总分</a></th>
  </tr>
<?php 
	$sql="select userinfo.uid,userinfo.nickname,userinfo.realname,userinfo.email from userinfo,compscore where compscore.uid=userinfo.uid and compscore.ctid=$_GET[ctid] order by uid";
	$cnt=$p->dosql($sql);
	$luid=0;
	$rowcnt=0;
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		if ($d[uid]==$luid) continue;
		$rowcnt++;
		$luid=$d[uid];
?>
  <tr>
    <td id="rank<?php echo $rowcnt ?>">&nbsp;</td>
    <td id="uid<?php echo $rowcnt ?>"><?php echo $d[uid] ?></td>
    <td id="nickname<?php echo $rowcnt ?>"><a href="comp.php?ctid=<?php echo $_GET['ctid'] ?>&uid=<?php echo $d['uid'] ?>" target="_blank">
<?=gravatar::showImage($d['email'], 14);?>
<?php echo $d['nickname'] ?></a></td>
	<?php if ($_SESSION['admin']>0) { ?>
    <td id="realname<?php echo $rowcnt ?>"><a href="../user/detail.php?uid=<?php echo $d['uid'] ?>" target="_blank"><?php echo $d['realname'] ?></a></td><?php } ?>
<?php
		$sql="select pid,result,score,csid from compscore where uid='{$d['uid']}' and ctid={$_GET[ctid]} order by pid asc";
		$cnt_sub=$q->dosql($sql);
		$sum=0;
		$rank=$mbarray;
		for ($j=0;$j<$cnt_sub;$j++)
		{
			$e=$q->rtnrlt($j);
			$sum+=$e[score];
			$rank[ $e[pid] ][score]=$e[score];
			$rank[ $e[pid] ][result]=$e[result];
		}
		foreach($pbs as $k=>$v)
		{
?>
    <td id="result<?=$v?>_<?=$rowcnt?>">
    <?php
    if ($rank[$v][result] =="") echo "未评测";
    else if ($rank[$v][result] =="N") echo judgetext($rank[$v][result]);
    else { ?>
    <a href="code.php?csid=<?=$e['csid']?>" target="_blank"><pre style='margin:0;'><?=judgeresult($rank[$v][result])?></pre></a></td>
    <? } ?>
    <td id="score<?php echo $v ?>_<?php echo $rowcnt ?>"><?php echo $rank[$v][score]; ?></td>
<?php
		}
?>
    <td id="sum<?php echo $rowcnt ?>"><?php echo $sum ?></td>
  </tr>
<?php 
	}
?>
</table>
  <script language="javascript">
var sortsx=true;
var rowcnt=<?php echo $rowcnt ?>;

function qsort(key)
{
	sortsx=!sortsx;
	vmin=10000;
	vmax=-1;
	if (sortsx)
	{
		for (i=1;i<=rowcnt-1;i++)
		{
			vi=new Number(document.getElementById(key+i).innerHTML);
			pmin=vmin;
			for (j=i;j<=rowcnt;j++)
			{
				vj=new Number(document.getElementById(key+j).innerHTML);
				if (vj<pmin)
				{
					pmin=vj;
					k=j;
				}
			}
			if (pmin<vi)
				swap(i,k);
		}
	}
	else
	{
		for (i=1;i<=rowcnt-1;i++)
		{
			vi=new Number(document.getElementById(key+i).innerHTML);
			pmax=vmax;
			for (j=i;j<=rowcnt;j++)
			{
				vj=new Number(document.getElementById(key+j).innerHTML);
				if (vj>pmax)
				{
					pmax=vj;
					k=j;
				}
			}
			if (pmax>vi)
				swap(i,k);
		}
	}
}

function swap(a,b)
{
	t=document.getElementById("uid"+a).innerHTML;
	document.getElementById("uid"+a).innerHTML=document.getElementById("uid"+b).innerHTML;
	document.getElementById("uid"+b).innerHTML=t;
	
	t=document.getElementById("nickname"+a).innerHTML;
	document.getElementById("nickname"+a).innerHTML=document.getElementById("nickname"+b).innerHTML;
	document.getElementById("nickname"+b).innerHTML=t;
	<?php if ($_SESSION['admin']>0) { ?>
	t=document.getElementById("realname"+a).innerHTML;
	document.getElementById("realname"+a).innerHTML=document.getElementById("realname"+b).innerHTML;
	document.getElementById("realname"+b).innerHTML=t;
	<?php } ?>
	t=document.getElementById("sum"+a).innerHTML;
	document.getElementById("sum"+a).innerHTML=document.getElementById("sum"+b).innerHTML;
	document.getElementById("sum"+b).innerHTML=t;

	t=document.getElementById("rank"+a).innerHTML;
	document.getElementById("rank"+a).innerHTML=document.getElementById("rank"+b).innerHTML;
	document.getElementById("rank"+b).innerHTML=t;
<?php
foreach($pbs as $k=>$v)
{
?>

	t=document.getElementById("result<?php echo $v ?>_"+a).innerHTML;
	document.getElementById("result<?php echo $v ?>_"+a).innerHTML=document.getElementById("result<?php echo $v ?>_"+b).innerHTML;
	document.getElementById("result<?php echo $v ?>_"+b).innerHTML=t;
	
	t=document.getElementById("score<?php echo $v ?>_"+a).innerHTML;
	document.getElementById("score<?php echo $v ?>_"+a).innerHTML=document.getElementById("score<?php echo $v ?>_"+b).innerHTML;
	document.getElementById("score<?php echo $v ?>_"+b).innerHTML=t;
<?php
}
?>

}

qsort("sum");
var last="";

for (i=1;i<=rowcnt;i++)
{
	if (last==document.getElementById("sum"+i).innerHTML)
	{
		document.getElementById("rank"+i).innerHTML=document.getElementById("rank"+(i-1)).innerHTML;
	}
	else
	{
		document.getElementById("rank"+i).innerHTML=i;
		last=document.getElementById("sum"+i).innerHTML;
	}
}
	
</script>
<p><a href="../information/help.php">评测结果说明</a></p>

<?php
	include_once("../include/stdtail.php");
?>
