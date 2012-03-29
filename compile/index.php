<?php
require_once("../include/stdhead.php");
gethead(1,"sess","编译执行");
$LIB->dpshhl();

/*if (!($_SESSION['admin']>0)) {
echo "系统正在调试中，请稍候使用，谢谢合作。CmYkRgB123";
exit;
}*/
if (!$_POST['pid']) {
echo "你来错地方了！";
include_once("../include/stdtail.php");
exit;
}

$LIB->cls_compile(); 
$LIB->func_socket();
?>

<?php
$p=new DataAccess();
$sql="select * from problem where pid={$_POST['pid']}";
$p->dosql($sql);
$d=$p->rtnrlt(0);
if (!$d['submitable'] && !$_SESSION['admin']>0) {
	echo '<script>document.location="../error.php?id=18"</script>';
	exit;
}

$lang=langstrtonum($_POST['lang']);

$info=array();
$info['pid']=$_POST['pid'];
$info['sid']=$_POST['sid'];
$info['uid']=$_SESSION['ID'];
$info['language']=$lang;
$info['pname']=$d['filename'];
$info['datacnt']=$d['datacnt'];
$info['timelimit']=$d['timelimit'];
$ptitle = $d['probname'];
$info['memorylimit']=$d['memorylimit'];
$info['plugin']=$d['plugin'];
$info['compiledir']=$SETTINGS['dir_source'];
$info['mode']="normal";
if ($_POST['testmode']=='1' && $_SESSION['admin']>0)
	$info['mode']="test";
if ($_POST['rejudge']==1)
{
	$info['rejudge']=1;
	$q=new DataAccess();
	$sql="select * from submit where sid='{$info['sid']}'";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	$info['uid']=$e['uid'];
	$src=$e['srcname'];
}
$Cp=new Compiler($info);

?>
<p>正在连接评测机……</p>
<p>
<?php
flush();
$free=$Cp->getgds();
if ($_SESSION['admin']==2) {
?>
<a href="#" title="<?php echo $Cp->cmds ?>">Commands</a>
<?php
}
if (!$free) //非空闲
{
	echo "当前没有空闲的评测机，请稍后重新提交。";
	include_once("../include/stdtail.php");
	exit;
}

$Cp->lock();
$Cp->getdir();
if ($_POST['rejudge']==1) {
	$Cp->get_rejudge_src($src);
} else if (!$Cp->getupload()) {
	echo "源代码上传失败。请检查文件大小 [ 1 Byte , 100 KB ]。";
	include_once("../include/stdtail.php");
	exit;
}
?></p>
<p>连接评测机成功！</p>
<table border="1">
  <tr>
    <td>GRID</td>
    <td><?php echo $Cp->state['grid']; ?></td>
  </tr>
  <tr>
    <td>名称</td>
    <td><?php echo $Cp->state['name']; ?></td>
  </tr>
  <tr>
    <td>系统版本</td>
    <td><?php echo $Cp->state['ver']; ?></td>
  </tr>
  <tr>
    <td>备注</td>
    <td><?php echo $Cp->state['memo']; ?></td>
  </tr>
</table>
</p>
<p>正在编译...</p>
<?php
flush();
$csucc=$Cp->compile();
?>
<?php
flush();
if ($csucc)
{
?>
<p>编译成功</p>
<p>
<table border='1'>
<tr>
	<td>测试点</td>
	<td>结果</td>
	<td>得分</td>
	<td>运行时间</td>
	<td>内存使用</td>
	<td>退出代码</td>
</tr>
<tr>
<?php
$nodata = false;
	for ($P=1;$P<=$d['datacnt'];$P++)
	{
		$Cp->run($P);
		flush();
        if($Cp->noindata > 0 || $Cp->noansdata > 0) {
            $nodata = true;
            ?>
	<td><?php echo $P;?></td>
	<td>该测试点没有数据！</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
            <?
        } else {
?>
	<td><?php echo $P;?></td>
	<td><?php echo $Cp->getresult(); ?></td>
	<td><?php echo $Cp->getthisscore(); ?></td>
	<td><?php printf("%.3f s",$Cp->runtime/1000.0) ?></td>
	<td><?php echo $Cp->memory ?> KB</td>
	<td><?php echo $Cp->exitcode?></td>
<?php 
	}
?>
</tr>
<?
    }
?>
</table>
</p>
<p>你的程序运行完成了！</p>
<p>运行时间 <?php printf ("%.3f",$Cp->gettotaltime()/1000.0) ?> s</p>
<p>平均内存 <?php printf("%.2f",$Cp->getmemory()/1024) ?> MiB</p>
<p>测试点通过状况 <a href="../problem/submitdetail.php?id=<?=$info['sid']?>"><?php echo judgeresult($Cp->s_detail) ?></a></p>
<p>得分：<?php echo $Cp->getscore(); ?></p>
<p><a class="LinkButton" href="../problem/pdetail.php?pid=<?php echo $_POST['pid'] ?>">返回原题 “<?=$ptitle?>”</a></p>
<?php if ($Cp->ac==$d['datacnt']) { ?>
    <p>祝贺你通过了全部测试点!</p>
<?php } else { if(($_SESSION['admin'] > 0 || $_SESSION['ID'] == $info['uid']) && $nodata == false) { ?>
<p>你在第<?=$Cp->wrongpoint?>个测试点出现了爆〇的情况，下面是该题的输入数据：
<pre><code class="no-highlight"><?=htmlspecialchars($Cp->inputtext)?></code></pre>
<p>下面是你的输出与标准答案不同的地方（上面带减号“-”的是你的输出，下面带加号“+”的是答案输出，“@@”之间的数字表示行号）：
<pre><code class="diff"><?=htmlspecialchars($Cp->difftext)?></code></pre>
<p><a class="LinkButton" href="../problem/pdetail.php?pid=<?php echo $_POST['pid'] ?>">返回原题 “<?=$ptitle?>”</a></p>
<?php } else echo "<p>你无法查看错误点的输入输出文件。</p>";?>
<p><a href="../information/help.php" target="_blank" title="RP问题">为什么程序在我的电脑上能够正常运行，而在评测机上不能?</a></p>
<?php }
if($nodata == false && ($_POST['testmode'] != 1 || $_SESSION['admin']==0))
	$Cp->writedb_single();
}
else {//编译失败
?>
<p>编译失败：</p>
<pre><?=htmlspecialchars($Cp->compilemessage)?></pre>
<?php 
}
$Cp->unlock();

$Dir=pathinfo($_SERVER['SCRIPT_FILENAME']);
chdir($Dir['dirname']."/../include");
?>


<?php
	include_once("../include/stdtail.php");
?>
