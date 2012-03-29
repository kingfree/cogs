<?php
require_once("../../include/stdhead.php");
gethead(1,"admin","成绩发布");

$LIB->cls_sendmail();

$ctid=$_REQUEST['ctid'];

$p=new DataAccess();
$q=new DataAccess();
$sql="update comptime set showscore=1 where ctid={$ctid}";
$p->dosql($sql);
?>
<p>[0%/100%]正在发送邮件...(请勿停止或关闭该窗口)</p>
<?
flush();
$sql="select compbase.contains,compbase.cname,comptime.starttime,comptime.endtime from compbase,comptime where comptime.cbid=compbase.cbid and comptime.ctid={$ctid}";

$p->dosql($sql);
$d=$p->rtnrlt(0);
$cname=$d['cname'];
$starttime=date('Y-m-d H:i:s',$d['starttime'] );
$endtime=date('Y-m-d H:i:s',$d['endtime'] );

$pbs=explode(":",$d['contains']);

$cnt_prob=0;
foreach($pbs as $k=>$v)
{
	$mbarray[$v][score]=0;
	$mbarray[$v][result]="N";
	$sql="select probname from problem where pid={$v}";
	$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$cnt_prob++;
	$probname[$cnt_prob]=$d['probname'];
}
$total=100*$cnt_prob;
$subject="你在{$SETTINGS['global_sitename']}比赛{$cname}的成绩";
$compinfo="<p>这是{$subject}。</p>\n<p>- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</p>\n<p>该比赛开始于{$starttime},结束于{$endtime}。</p>\n<p>共{$cnt_prob}道题</p>";
for ($i=1;$i<=$cnt_prob;$i++)
	$compinfo.="<p>{$probname[$i]} 100</p>";
$compinfo.="<p>总分 {$total}</p>";

$expression="说明"."<table border='1'>
  <tr>
    <td>A</td>
    <td>正确</td>
  </tr>
  <tr>
    <td>W</td>
    <td>错误</td>
  </tr>
  <tr>
    <td>T</td>
    <td>超过时间限制</td>
  </tr>
  <tr>
    <td>E</td>
    <td>运行时出错</td>
  </tr>
  <tr>
    <td>R</td>
    <td>没有输出文件</td>
  </tr>
  <tr>
    <td>C</td>
    <td>编译失败</td>
  </tr>
  <tr>
    <td>N</td>
    <td>没有找到源文件</td>
  </tr>
  <tr>
    <td>P</td>
    <td>获得部分得分(评测插件)</td>
  </tr>
  <tr>
    <td>M</td>
    <td>超过内存限制</td>
  </tr>
</table>";

$sql="select userinfo.uid,userinfo.nickname,userinfo.realname,userinfo.email from userinfo,compscore where compscore.uid=userinfo.uid and compscore.ctid=$ctid order by uid";
$cnt=$p->dosql($sql);
$luid=0;
$rowcnt=0;
for ($i=0;$i<$cnt;$i++)
{
	$d=$p->rtnrlt($i);
	if ($d['uid']==$luid) continue;
	$rowcnt++;
	$luid=$d[uid];
	$mailinfo="<p>你好,{$d['nickname']}</p>".$compinfo."\n<p>- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</p>\n<p>这是你的成绩</p>";
	$sql="select pid,result,score from compscore where uid='{$d['uid']}' and ctid={$ctid} order by pid asc";
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
		$t=$k+1;
		$mailinfo.="<p>{$probname[$t]} {$rank[$v][result]} {$rank[$v][score]} </p>";
	}
	$mailinfo.="<p>总分 {$sum} </p>\n<p>- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</p>";
	$mailinfo.=$expression;
	$progress=round(($i+1)*100/(float)$cnt,2);
	echo "<p>[{$progress}%/100%]正在向{$d['nickname']}发送成绩...";
	flush();
	$succ=sendmail($d['email'],$subject,$mailinfo);
	if ($succ)
		echo "发送成功";
	else 
		echo "发送失败";
	echo "</p>";
	flush();
}
echo "<p>[100%/100%]全部发送完成</p>";

?>

<?php
	include_once("../../include/stdtail.php");
?>