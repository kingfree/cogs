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

<?php if ($_GET['caid']!="") {
    $sql="select * from category where caid={$_GET['caid']}";
    $cnt=$p->dosql($sql);
    $d=$p->rtnrlt(0);
?>

<span id="cate_detail">当前分类：<span style="font-size:20px;"><?php echo $d['cname'] ?></span>（<?php echo nl2br(sp2n(htmlspecialchars($d['memo']))) ?>）</span>
<?php } ?>
<?php if(有此权限('修改题目')) { ?>
<span class="admin_big"><a href="../admin/problem/editprob.php?action=add">添加新题目</a></span>
<?php } ?>

<?php
$sql="select problem.* from problem";
if($_GET['caid'])
$sql.=",tag where tag.pid=problem.pid and tag.caid={$_GET['caid']}";
else
$sql .= " where problem.readforce>=0";

if ($_GET['key']!="")
$sql.=" and (problem.probname like '%{$_GET[key]}%' or problem.pid ='{$_GET[key]}' or problem.filename like '%{$_GET[key]}%')";

if ($_GET['diff']!="")
$sql.=" and problem.difficulty='{$_GET['diff']}'";

if($_GET['rank']=="按题目名称排序")
    $sql.=" order by probname asc";
else if($_GET['rank']=="按文件名称排序")
    $sql.=" order by filename asc";
else if($_GET['rank']=="按评测方式排序")
    $sql.=" order by plugin asc";
else if($_GET['rank']=="按时间限制排序")
    $sql.=" order by timelimit asc";
else if($_GET['rank']=="按空间限制排序")
    $sql.=" order by memorylimit asc";
else if($_GET['rank']=="按题目难度排序")
    $sql.=" order by difficulty asc";
else if($_GET['rank']=="按通过次数排序")
    $sql.=" order by acceptcnt desc";
else if($_GET['rank']=="按可否提交排序")
    $sql.=" order by submitable asc";
else if($_GET['rank']=="按阅读权限排序")
    $sql.=" order by readforce desc";
else
    $sql.=" order by problem.pid asc";

$cnt=$p->dosql($sql);
$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
if(!$_GET['page']) {
    $_GET['page']=1;
    $st=0;
} else {
    if ($_GET[page]<1 || $_GET[page]>$totalpage)
        异常("页面错误！");
    else
        $st=(($_GET[page]-1)*$SET['style_pagesize']);
}
?>
<form id="search_prob" name="search_prob" method="get" action="">
难度 
<select name="diff" id="diff" onchange="MM_jumpMenu_2('parent',this,0)">
<option value="" selected="selected" <?php if ($_GET['diff']=="") {?> selected="selected"<?php } ?> >全部</option>
<?php for ($i=1;$i<=10;$i++) { ?>
<option value="<?php echo $i;?>" <?php if ($_GET['diff']==$i) {?> selected="selected"<?php } ?> ><?php echo 难度($i);?></option>
<?php } ?>
</select>
搜索题目
<input name="key" type="text" id="key" value="<?php echo $_GET['key'] ?>" />
<input name="sc" type="submit" id="sc" value="搜索"/>
<input name="caid" type="hidden" id="caid" value="<?php echo $_GET['caid'] ?>" />
<a href="random.php" title="随机选择一道你没有通过的题目">随机题目</a>
</form>
<form id="rank" action="" method="get" name="rank">
  <input name="rank" type="submit" value="按题目名称排序" />
  <input name="rank" type="submit" value="按文件名称排序" />
  <input name="rank" type="submit" value="按评测方式排序" />
  <input name="rank" type="submit" value="按时间限制排序" />
  <input name="rank" type="submit" value="按空间限制排序" />
  <input name="rank" type="submit" value="按题目难度排序" />
  <input name="rank" type="submit" value="按通过次数排序" />
  <?php if(有此权限('查看题目')) { ?>
  <input name="rank" type="submit" value="按可否提交排序" />
  <? } ?>
</form>
<? 分页($cnt, $_GET['page'], '?caid='.$_GET['caid'].'&diff='.$_GET['diff'].'&key='.$_GET['key'].'&rank='.$_GET['rank'].'&'); ?>
<table id="problist">
<thead><tr>
<th>PID</th>
<th onclick="sortTable('problist', 0, 'int')">题目名称</th>
<th>文件名称</th>
<th>时间限制</th>
<th>空间限制</th>
<th>难度</th>
<th onclick="sortTable('problist', 6, 'int')">通过</th>
<th onclick="sortTable('problist', 7, 'int')">提交</th>
<th onclick="sortTable('problist', 8, 'int')">通过率</th>
<?php if(有此权限('查看题目')) { ?>
<th class=admin>标识</th>
<th class=admin>权限</th>
<?php } ?>
<?php if(有此权限('修改题目')) { ?>
<th class=admin>编辑</th>
<?php } ?>
</tr></thead>
<?php
if (!$err) for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=$p->rtnrlt($i);
    if($_GET['key'] && $cnt == 1)
        echo "<script language='javascript'>location='problem.php?pid={$d['pid']}';</script>";
    if (!$d['submitable'] && !有此权限('查看题目')) continue;
    if ($d['readforce']>$_SESSION['readforce'] && !有此权限('查看题目')) continue;
?>
<tr>
<td align=center><?php echo $d['pid'] ?></td>
<td><? 是否通过($d['pid'], $q);?><b><a href="problem.php?pid=<?=$d['pid'] ?>"><?=$d['probname'] ?></a></b></td>
<td align=center><?php echo $d['filename']; ?></td>
<td align=center><?php echo $d['timelimit']/1000 . " s"; ?></td>
<td align=center><?php echo $d['memorylimit'] . " MiB"; ?></td>
<td><?php echo 难度($d['difficulty']); ?></td>
<td align=center><?php echo $d['acceptcnt']; ?></td>
<td align=center><?php echo $d['submitcnt']; ?></td>
<td align=center><?php echo @round($d['acceptcnt']/$d['submitcnt']*100,2); ?>%</td>
<?php if(有此权限('查看题目')) { ?>
<td class=admin align=center>
<?php if ($d['submitable']) echo "<span class=ok>可提交</span>"; else echo "<span class=no>不可提交</span>"; ?>
</td>
<td class=admin align=center><?=$d['readforce']?></td>
<?php } ?>
<?php if(有此权限('修改题目')) { ?>
<td class=admin align=center><a href="../admin/problem/editprob.php?action=edit&pid=<?=$d['pid']; ?>">修改</a></td>
<?php } ?>
</tr>
<?php } ?>
</table>

<?php
include_once("../include/stdtail.php");
?>
