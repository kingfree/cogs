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

<center>
<table width="100%" border="0" cellspacing=0 cellpadding=4>
<tr>
<td width="15%">题目名称</td>
<td width="35%" scope="col"><?php echo $d[probname]; ?></td>
<td width="15%">难度等级</td>
<td width="35%"><?php echo difficulty($d['difficulty']); ?></td>
</tr>
<tr>
<td>输入文件</td>
<td><?php echo $d[filename]; ?>.in</td>
<td>输出文件</td>
<td><?php echo $d[filename]; ?>.out</td>
</tr>
<tr>
<td>时间限制</td>
<td><?php echo $d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td>
<td>内存限制</td>
<td><?php echo $d['memorylimit']; ?> MB </td>
</tr>
<tr>
<td>对比方式</td>
<td><?php echo $STR['plugin'][$d['plugin']]; ?></td>
<td>测试点数</td>
<td><?php echo $d[datacnt]; ?></td>
</tr>
<tr>
<td>提交情况</td>
<td><?php if ($d['submitable']) echo "可提交"; else echo "不可提交"; ?></td>
<td>加入时间</td>
<td><?php echo date('Y-m-d', $d['addtime']) ?></td>
</tr>
<tr>
<td>开放分组</td>
<td><a href="../information/userlist.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
<?php if ($_SESSION['ID']){ ?>
<th><?php
$sql="SELECT * FROM submit WHERE pid ={$d['pid']} AND uid ={$_SESSION['ID']} order by accepted desc limit 1";
$ac=$q->dosql($sql);
if ($ac) {
$e=$q->rtnrlt(0);
?><a href="submitdetail.php?id=<?php echo $e['sid'] ?>" target='_blank'>
<?php
if ($e['accepted']) {?> <img src='../images/sign/right.gif' border="0" />已解决<? }
else {?> <img src='../images/sign/error.gif' border="0" />未解决<?php } ?></a>
<? } else { ?><img src='../images/sign/todo.gif' />未提交 <? } ?>
</th><?php } else {?><td></td><? } ?>
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
<?php if ($_SESSION['admin']>0){ ?>
<td bgcolor="#99FFCC"><a href="../admin/problem/editprob.php?action=edit&pid=<?php echo  $d[pid]; ?>">修改该题</a></td>
<?php } else { ?>
<td></td>
<? } ?>
<td>
<a href="comments.php?pid=<?=$d['pid']?>" target="_blank">[参与题目讨论，发表看法]</a>
</td>
</tr>
<tr style="background-color:#F0F8FF;">
<form action="../compile/" method="post" enctype="multipart/form-data" name="sub">
<td>
<?php if ($_SESSION['admin']>0){ ?>
<input name="testmode" type="checkbox" id="testmode" value="1" /> 
<label for="testmode">测试模式</label>
<?php } ?>
</td>
<td colspan="2">代码：
<input type="file" name="file" class="Button"/>
<input type="radio" name="lang" id="pas" value="pas" /><label for="pas">Pascal</label>
<input type="radio" name="lang" id="c" value="c" /><label for="c">C</label>
<input type="radio" name="lang" id="cpp" value="cpp" checked=1/><label for="cpp">C++</label>
</td><td>
<input class="LinkButton" type="submit" name="Submit" value="提交"/>
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']; ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="102400">
</td></form>
</tr>
</table>
</center>
<h3>题目描述</h3>
<div class="MainText"><?php echo $d[detail] ?></div>
<div id="singlerank">
<h3>运行速度 Top <?php echo $SETTINGS['style_single_ranksize']; ?></h3>
<table border="1" width="100%">
<tr>
<th width="33%" scope="col">Pascal</th>
<th width="33%" scope="col">C</th>
<th width="34%" scope="col">C++</th>
</tr>
<tr>
<td valign="top"><?php $LIB->singlerank($p,$_GET['pid'],0) ?></td>
<td valign="top"><?php $LIB->singlerank($p,$_GET['pid'],1) ?></td>
<td valign="top"><?php $LIB->singlerank($p,$_GET['pid'],2) ?></td>
</tr>
</table>
</div>

<?php
include_once("../include/stdtail.php");
?>
