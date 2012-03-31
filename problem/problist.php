<?php
require_once("../include/stdhead.php");
gethead(1,"","题目列表");
$p=new DataAccess();
$q=new DataAccess();
?>

<script type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){
eval(targ+".location='?diff=<?php echo $_GET['diff'] ?>&caid=<?php echo $_GET['caid'] ?>&key=<?php echo $_GET['key'] ?>&page="+selObj.options[selObj.selectedIndex].value+"'");
if (restore) selObj.selectedIndex=0;
}
function MM_jumpMenu_2(targ,selObj,restore){
eval(targ+".location='?caid=<?php echo $_GET['caid'] ?>&key=<?php echo $_GET['key'] ?>&diff="+selObj.options[selObj.selectedIndex].value+"'");
if (restore) selObj.selectedIndex=0;
}
//-->
</script>

<?php if ($_GET['caid']!=""){ 
$sql="select * from category where caid={$_GET['caid']}";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);
?>
当前分类：<span style="font-size:20px;"><?php echo $d['cname'] ?></span>（<?php echo nl2br(sp2n(htmlspecialchars($d['memo']))) ?>）
<?php } ?>
<?php if ($_SESSION['admin']>0){ ?>
<a class="adminButton" href="../admin/problem/editprob.php?action=add">添加新题目</a>
<?php } ?>

<?php
$sql="update problem set lastacid=1 where lastacid=0";
$p->dosql($sql);

if ((int)$_GET['caid']!=0)
$sql="select problem.*,userinfo.nickname as name,userinfo.uid from problem,userinfo,tag where tag.pid=problem.pid and tag.caid={$_GET['caid']} and";
else
$sql="select problem.*,userinfo.nickname as name,userinfo.uid from problem,userinfo where";

$sql.=" userinfo.uid=problem.lastacid";

if ($_GET['key']!="")
$sql.=" and (problem.probname like '%{$_GET[key]}%' or problem.pid ='{$_GET[key]}' or problem.filename like '%{$_GET[key]}%')";

if ($_GET['diff']!="")
$sql.=" and difficulty='{$_GET['diff']}'";

$sql.=" order by problem.pid asc";

$cnt=$p->dosql($sql);
$totalpage=(int)(($cnt-1)/$SETTINGS['style_pagesize'])+1;
if ($_GET['page']=="") 
{
$_GET['page']=1;
$st=0;
}
else 
{
if ($_GET[page]<1 || $_GET[page]>$totalpage)
{
echo "页错误！";
$err=1;
}
else
$st=(($_GET[page]-1)*$SETTINGS['style_pagesize']);
}
?>
<form id="form1" name="form1" method="get" action="">
难度 
<select name="diff" class="InputBox" id="diff" onchange="MM_jumpMenu_2('parent',this,0)">
<option value="" selected="selected" <?php if ($_GET['diff']=="") {?> selected="selected"<?php } ?> >全部</option>
<?php
for ($i=1;$i<=10;$i++)
{
?>
<option value="<?php echo $i;?>" <?php if ($_GET['diff']==$i) {?> selected="selected"<?php } ?> ><?php echo difficulty($i);?></option>
<?php
}
?>
</select>
搜索题目
<input name="key" type="text" id="key" class="InputBox" value="<?php echo $_GET['key'] ?>" />
<input class="LinkButton" name="sc" type="submit" id="sc" value="搜索"/>
<input name="caid" type="hidden" id="caid" value="<?php echo $_GET['caid'] ?>" />
</form>
<? page_slice($cnt, $_GET['page'], '?caid='.$_GET['caid'].'&diff='.$_GET['diff'].'&key='.$_GET['key'].'&sc'.$_GET['sc'].'&'); ?>
<table width="100%" border="1">
<tr>
<th scope="col">PID</th>
<?php if ($_SESSION['ID']){ ?>
<th scope="col">个人状态</th>
<?php } ?>
<th scope="col">题目名称</th>
<th scope="col">文件名称</th>
<th scope="col">时间限制</th>
<th scope="col">空间限制</th>
<th scope="col">难度</th>
<th scope="col">通过</th>
<th scope="col">提交</th>
<th scope="col">通过率</th>
<th scope="col">上次通过</th>
<?php if ($_SESSION['admin']>0){ ?>
<th scope="col" style=admin>标识</th>
<th scope="col" style=admin>权限</th>
<?php } ?>
</tr>
<?php
if (!$err)
for ($i=$st;$i<$cnt && $i<$st+$SETTINGS['style_pagesize'] ;$i++)
{

$d=$p->rtnrlt($i);
if($_GET['key'] && $cnt == 1) {
echo "<script language='javascript'>location='pdetail.php?pid={$d['pid']}';</script>";
}
if (!$d['submitable'] && !$_SESSION['admin']>0) continue;
if ($d['readforce']>$_SESSION['readforce'] && !($_SESSION['admin']>0)) continue;
?>
<tr>
<td><?php echo $d['pid'] ?></td>
<?php if ($_SESSION['ID']){ ?>
<td align=center><?php
$sql="SELECT * FROM submit WHERE pid ={$d['pid']} AND uid ={$_SESSION['ID']} order by accepted desc limit 1";
$ac=$q->dosql($sql);
if ($ac)
{
$e=$q->rtnrlt(0);
?><a href="submitdetail.php?id=<?php echo $e['sid'] ?>" target='_blank'>
<?php
if ($e['accepted']) {?> <img src='../images/sign/right.gif' border="0" />已解决 <? }
else {?> <img src='../images/sign/error.gif' border="0" />未解决 <?php }
?>
</a>
<?php
}
else { ?><img src='../images/sign/todo.gif' />未提交 <? }
?></td><?php } ?>
<td><a href="pdetail.php?pid=<?php echo $d['pid'] ?>"><?php echo $d['probname'] ?></a></td>
<td><?php echo $d['filename']; ?></td>
<td align=center><?php echo $d['timelimit']/1000 . " s"; ?></td>
<td align=center><?php echo $d['memorylimit'] . " MiB"; ?></td>
<td><?php echo difficulty($d['difficulty']); ?></td>
<td align=center><?php echo $d['acceptcnt']; ?></td>
<td align=center><?php echo $d['submitcnt']; ?></td>
<td align=center><?php echo @round($d['acceptcnt']/$d['submitcnt']*100,2); ?>%</td>
<td><?php if ($d['uid']==1){ echo "无"; } else { ?>
<a href="../user/detail.php?uid=<?php echo $d['uid'] ?>" target="_blank"><?php echo $d['name'];} ?></a></td>
<?php if ($_SESSION['admin']>0){ ?>
    <td style=admin>
        <?php if ($d['submitable']) echo "可提交"; else echo "不可提交"; ?>
    </td>
    <td style=admin><?=$d['readforce']?>
    </td>
    <?php } ?>
    </tr>
    <?php
}
?>
</table>
</p>

<?php
include_once("../include/stdtail.php");
?>
