<?php
require_once("../include/stdhead.php");
gethead(1,"sess","编译执行");
$LIB->dpshhl();

if (!$_POST['pid']) 异常("你来错地方了！");

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
$info['compiledir']=$SET['dir_source'];
$info['mode']="normal";
if ($_POST['testmode']=='1' && $_SESSION['admin']>0)
	$info['mode']="test";
if ($_POST['rejudge']==1) {
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
<table border="0"><tr>
<td width=60%>
正在连接评测机...
<?php
flush();
$free=$Cp->getgds($_POST['judger']);

if (!$free) 异常("当前没有空闲的评测机，请稍后重新提交。");

$Cp->lock();
$Cp->getdir();
if ($_POST['rejudge']==1) {
	$Cp->get_rejudge_src($src);
} else if (!$Cp->getupload()) {
	异常("源代码上传失败。请检查文件大小 [ 1 Byte , 100 KB ]。");
}
?>
<table border="1">
  <tr>
    <td>GRID</td>
    <td><?=$Cp->state['grid']; ?></td>
  </tr>
  <tr>
    <td>名称</td>
    <td><?=$Cp->state['name']; ?></td>
  </tr>
  <tr>
    <td>系统版本</td>
    <td><?=$Cp->state['ver']; ?></td>
  </tr>
  <tr>
    <td>备注</td>
    <td><?=$Cp->state['memo']; ?></td>
  </tr>
</table>
正在编译...
<?php
flush();
$csucc=$Cp->compile();
flush();
if ($csucc) {
?>
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
	for ($P=1;$P<=$d['datacnt'];$P++) {
		$Cp->run($P);
		flush();
?>
	<td><?=$P;?></td>
	<td><?=$Cp->getresult(); ?></td>
	<td><?=$Cp->getthisscore(); ?></td>
	<td><?php printf("%.3f s",$Cp->runtime/1000.0) ?></td>
	<td><?=$Cp->memory ?> KB</td>
	<td><?=$Cp->exitcode?></td>
</tr>
<?
    }
?>
</table>
</td>
<td width=40%>
<p>运行时间 <?php printf ("%.3f",$Cp->gettotaltime()/1000.0) ?> s</p>
<p>平均内存 <?php printf("%.2f",$Cp->getmemory()/1024) ?> MiB</p>
<p>测试点通过状况 <a href="../problem/submitdetail.php?id=<?=$info['sid']?>"><?=评测结果($Cp->s_detail) ?></a></p>
<p>得分：<?=$Cp->getscore(); ?></p>
<p><a href="../problem/pdetail.php?pid=<?=$_POST['pid'] ?>">返回原题 “<?=$ptitle?>”</a></p>
<?php if ($Cp->ac==$d['datacnt']) {
    $AC = 1;
} else {
    $AC = 0;
    if(($_SESSION['admin'] > 0 || $_SESSION['ID'] == $info['uid']) &&
            $nodata == false) {
        $AC = -1;
    } 
}?>
<?php if($AC == 1) { ?>
    <p class="ok">祝贺你通过了全部测试点！</p>
<?php } else if($AC < 1) { ?>
    <p class="no">你没有通过这道题！</p>
    <p><a href="../information/help.php" target="_blank" title="RP问题">为什么程序在我的电脑上能够正常运行，而在评测机上不能?</a></p>
<?php } ?>
<?php
if($nodata == false && ($_POST['testmode'] != 1 || $_SESSION['admin']==0))
	$Cp->writedb_single();
else echo "<p class=no>没有写入数据库</p>";
?>
</td>
</tr></table>
<?php if($AC == -1) { ?>
<p class="no">你在第<?=$Cp->wrongpoint?>个测试点出现了爆零的情况，下面是该题的输入数据：
<pre class="brush: text;"><?=htmlspecialchars($Cp->inputtext)?></pre>
<p>下面是你的输出与标准答案不同的地方（上面带减号“-”的是你的输出，下面带加号“+”的是答案输出，“@@”之间的数字表示行号）：
<pre class="brush: diff;"><?=htmlspecialchars($Cp->difftext)?></pre>
<p><a href="../problem/pdetail.php?pid=<?=$_POST['pid'] ?>">返回原题 “<?=$ptitle?>”</a></p>
<?php } ?>
<?php } else { ?>
<p>编译失败：</p>
<pre class="brush: bash;"><?=htmlspecialchars($Cp->compilemessage)?></pre>
<?php 
}
$Cp->unlock();

$Dir=pathinfo($_SERVER['SCRIPT_FILENAME']);
chdir($Dir['dirname']."/../include");
?>
<script type="text/javascript">SyntaxHighlighter.all();</script>

<?php
	include_once("../include/stdtail.php");
?>
