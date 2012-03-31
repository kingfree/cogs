<?php
require_once("../include/stdhead.php");
gethead(1,"","题目详细信息");
?>

<?php
$p=new DataAccess();
$q=new DataAccess();
$sql="select problem.*,groups.* from problem,groups where pid={$_GET[pid]} and groups.gid=problem.group";
$cnt=$p->dosql($sql);
if ($cnt)
{
$d=$p->rtnrlt(0);
if ($d[readforce]>$_SESSION[readforce])
{
echo '<script>document.location="../error.php?id=17"</script>';
exit;
}
if (!$d[submitable] && !($_SESSION['admin']>0))
{
echo '<script>document.location="../error.php?id=18"</script>';
exit;
}

$subgroup=$LIB->getsubgroup($q,$d['gid']);
$subgroup[0]=$d['gid'];
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


$pid=$d[pid];
?>

<?php
} else {
echo '<script>document.location="../error.php?id=11"</script>';
}
?>

<div id='problem'>
<table class="pdetail" width=100% border=0><tr>
<td width=250px valign=top>
<table width="100%" style="margin: 2px 0;">
<tr>
<td width=70px>题目名称</td>
<td><?php echo $d[probname]; ?></td>
</tr>
<tr>
<td>难度等级</td>
<td><?php echo difficulty($d['difficulty']); ?></td>
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
<td><?php echo $d['memorylimit']; ?> MB </td>
</tr>
<tr>
<td>对比方式</td>
<td><?php echo $STR['plugin'][$d['plugin']]; ?></td>
</tr>
<tr>
<td>测试点数</td>
<td><?php echo $d[datacnt]; ?></td>
</tr>
<tr>
<td>加入时间</td>
<td><?php echo date('Y-m-d', $d['addtime']) ?></td>
</tr>
<tr>
<td>开放分组</td>
<td><a href="../information/userlist.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
</tr>
<tr>
<?php if ($_SESSION['ID']){ ?>
<td><?php
$sql="SELECT * FROM submit WHERE pid ={$d['pid']} AND uid ={$_SESSION['ID']} order by accepted desc limit 1";
$ac=$q->dosql($sql);
if ($ac) {
$e=$q->rtnrlt(0);
?><a href="submitdetail.php?id=<?php echo $e['sid'] ?>" target='_blank'>
<?php
if ($e['accepted']) {?> <img src='../images/sign/right.gif' border="0" />已解决<? }
else {?> <img src='../images/sign/error.gif' border="0" />未解决<?php } ?></a>
<? } else { ?><img src='../images/sign/todo.gif' />未提交 <? } ?>
</td><?php } else {?><td></td><? } ?>
<td><a href="../information/submitlist.php?pid=<?php echo $pid; ?>">提交状态</a></td>
</tr>
<tr>
<td>所属分类</td>
<td>
<?php
$sql="select category.cname,category.caid from category,tag where tag.pid={$_GET[pid]} and category.caid=tag.caid";
$r=new DataAccess();
$cnt2=$r->dosql($sql);
for ($i=0;$i<=$cnt2-1;$i++)
{
$e=$r->rtnrlt($i);
echo " <a href='problist.php?caid={$e[caid]}'>{$e[cname]}</a> ";
}
?></td>
</tr>
<tr>
<?php if ($_SESSION['admin']>0){ ?>
<td border=1><a href="../admin/problem/editprob.php?action=edit&pid=<?php echo  $d[pid]; ?>" class="LinkButton">修改该题</a></td>
<?php } else { ?>
<td></td>
<? } ?>
<td align=right>
<a href="comments.php?pid=<?=$pid?>"><b>发表看法</b></a>
</td>
</tr>
<tr>
<form action="../compile/" method="post" enctype="multipart/form-data" name="sub">
<td colspan=2 align=right>
<input type="file" name="file" class="Button"/>
<input type="radio" name="lang" id="pas" value="pas" /><label for="pas">Pascal</label>
<input type="radio" name="lang" id="c" value="c" /><label for="c">C</label>
<input type="radio" name="lang" id="cpp" value="cpp" checked=1/><label for="cpp">C++</label>
<?php if ($_SESSION['admin']>0){ ?>
<input name="testmode" type="checkbox" id="testmode" value="1" /> 
<label for="testmode">测试模式</label>
<?php } ?>
<input class="LinkButton" type="submit" name="Submit" style="font-size: 24px;" value="提交代码"/>
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']; ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="102400">
</td></form>
</tr>
</table>
<div id="singlerank">
<h3>运行速度 Top <?php echo $SETTINGS['style_single_ranksize']; ?></h3>
<table border="1" width="100%">
<tr><th>C++</th></tr>
<tr><td valign="top"><?php $LIB->singlerank($p,$_GET['pid'],2) ?></td></tr>
<tr><th>Pascal</th></tr>
<tr><td valign="top"><?php $LIB->singlerank($p,$_GET['pid'],0) ?></td></tr>
<tr><th>C</th></tr>
<tr><td valign="top"><?php $LIB->singlerank($p,$_GET['pid'],1) ?></td></tr>
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
