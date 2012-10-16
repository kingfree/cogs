<?php
require_once("../include/header.php");
gethead(1,"sess","评测");
$LIB->hlighter();
if (!$_POST['pid']) 异常("你来错地方了！");
$LIB->cls_compile(); 
$LIB->func_socket();
$p=new DataAccess();
$q=new DataAccess();
$sql="select * from problem where pid={$_POST['pid']}";
$p->dosql($sql);
$d=$p->rtnrlt(0);
if(!$d['submitable'] && !有此权限('查看题目'))
    异常("不可提交！",取路径("problem/index.php"));
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
if ($_POST['testmode']=='1' && 有此权限('测试题目'))
	$info['mode']="test";
if ($_POST['rejudge']==1) {
	$info['rejudge']=1;
	$sql="select * from submit where sid='{$info['sid']}'";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	$info['uid']=$e['uid'];
	$src=$e['srcname'];
}
$Cp=new Compiler($info);

?>
<div class='row-fluid'>
<div class='span6'>
<div class='alert alert-info'>
正在连接评测机...
<?php
flush();
$free=$Cp->getgds($_POST['judger']);
if (!$free) {
//$Cp->unlock();
    异常("当前没有空闲的评测机，请稍后重新提交。", 取路径("problem/problem.php?pid={$_POST['pid']}"));
}
$Cp->lock();
$Cp->getdir();
if ($_POST['rejudge']==1) {
	$Cp->get_rejudge_src($src);
} else if (!$Cp->getupload()) {
    $Cp->unlock();
	异常("源代码上传失败。请检查文件大小。", 取路径("problem/problem.php?pid={$_POST['pid']}"));
}
?>
<span class='badge badge-info'><?=$Cp->state['grid']?></span>
<span class='label label-info'><?=$Cp->state['name']?>
 <?=$Cp->state['ver']?></span>
<?=$Cp->state['memo']?>
</div>
<div class='alert alert-success'>
正在编译...
<?php
flush();
$csucc=$Cp->compile();
flush();
if ($csucc) {
?>开始运行
<table class='table table-condensed'>
<tr>
<th>点</th>
<th>结果</th>
<th>得分</th>
<th>运行时间</th>
<th>内存使用</th>
<th>返回</th>
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
</div>
</div>
<div class='span6'>
<div class='alert'>
<p>运行时间 <?php printf ("%.3f",$Cp->gettotaltime()/1000.0) ?> s</p>
<p>平均内存 <?php printf("%.2f",$Cp->getmemory()/1024) ?> MB</p>
<p>测试点通过状况 <a href="../submit/code.php?id=<?=$info['sid']?>"><?=评测结果($Cp->s_detail, 30) ?></a></p>
<p>得分：<b><?=$Cp->getscore(); ?></b></p>
<p><a href="../problem/problem.php?pid=<?=$_POST['pid'] ?>">返回原题 “<?=$ptitle?>”</a></p>
<?php if ($Cp->ac==$d['datacnt']) {
    $AC = 1;
} else {
    $AC = 0;
    if(($_SESSION['ID'] == $info['uid'] || 有此权限('查看数据')) &&
            $nodata == false) {
        $AC = -1;
    } 
}?>
<?php if($AC == 1) { ?>
    <p class="ok">祝贺你通过了全部测试点！</p>
    <p>顺便<a href="../problem/problem.php?pid=<?=$_POST['pid'] ?>">返回</a>为该题添加一个标签吧！</p>
    <p>...或者看看<a href="../problem/comments.php?pid=<?=$_POST['pid'] ?>">其他人的评论</a>，<a href="../problem/comment.php?pid=<?=$_POST['pid'] ?>">发表</a>一下你的感想吧！</p>
<?php } else if($AC < 1) { ?>
    <p class="no">你没有通过这道题！</p>
    <p><a href="../docs/" target="_blank" title="RP问题">为什么程序在我的电脑上能够正常运行，而在评测机上不能?</a></p>
<?php }
if($AC == -1) { ?>
<form action="testdata.php" method="post" class='form-inline'>
<span class='badge'>第<?=$Cp->wrongpoint?>个</span>测试点数据：
<input name="filename" type="hidden" value="<?=$Cp->filename?>" />
<input name="point" type="hidden" value="<?=$Cp->wrongpoint?>" />
<input name="io" type="submit" value="in" class='btn btn-success'/>
<input name="io" type="submit" value="ans" class='btn btn-success'/>
</form>
<?php } ?>
<?php
if($nodata == false && ($_POST['testmode'] != 1 || !有此权限('测试题目'))) {
	$Cp->writedb_single();
} else echo "<p class=no>没有写入数据库</p>";
?>
</div>
</div>
</div>
<?php if($AC == -1) { ?>
<div class='alert'>
<p class="no">你在<span class='badge'>第<?=$Cp->wrongpoint?>个</span>测试点出现了爆零的情况，下面是该题的输入数据：
<pre class="syntax plain"><?=htmlspecialchars(substr($Cp->inputtext, 0, 512))?>...</pre>
<p>下面是你的输出与标准答案不同的地方（上面带减号“-”的是你的输出，下面带加号“+”的是答案输出，“@@”之间的数字表示行号）：
<pre class="syntax diff"><?=htmlspecialchars(substr($Cp->difftext, 0, 512))?>...</pre>
<p><a href="../problem/problem.php?pid=<?=$_POST['pid'] ?>">返回原题 “<?=$ptitle?>”</a></p>
</div>
<?php } ?>
<?php } else { ?>
</div>
</div>
</div>
<div class='alert'>
<p>编译失败：</p>
<pre class="syntax bash"><?=htmlspecialchars($Cp->compilemessage)?></pre>
</div>
<?php 
}
$Cp->unlock();
?>
</div>
<script type="text/javascript">SyntaxHighlighter.all();</script>
</div>
<div class='footer'>
<?php echo 输出文本("进程运行 %processtime% s ，处理完成数据库 %querytimes% 次。") ?>
由 <a href="https://github.com/KingFree/COGS-by-Kingfree" target="_blank">CmYkRgB123 在线评测系统</a> 强力驱动，版本 <?php echo $cfg['Version']; ?> 。
</div>
</div>
</body>
</html>
