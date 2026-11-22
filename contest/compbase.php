<?php
require_once("../include/header.php");
gethead(1,"修改比赛","修改比赛");
?>
<div class='row-fluid'>
<script type="text/JavaScript">
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  window.location.assign(selObj.value);
  if (restore) selObj.selectedIndex=0;
}
</script>
<a href="editcompbase.php?action=add" class="btn btn-info pull-left">添加新比赛</a>
<?php
	$contests = Contest::withOrganizers();
$problems = array_column(Problem::choices(), null, 'pid');
$rounds = ContestRound::byContest();

$cnt = count($contests);
$st=检测页面($cnt, ($_GET['page'] ?? 1));
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th width="80px">CBID</th>
    <th width="140px">比赛名</th>
    <th>包含题目</th>
    <th>关联场次</th>
    <th width="80px">组织者</th>
    <th width="60px">操作</th>
  </tr>
<?php
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
		$d=($contests[$i] ?? false);
?>
  <tr>
    <td><?php echo $d['cbid'] ?></td>
    <td><?=h($d['cname'])?></td>
    <td>
        <select name="menu1" onchange="MM_jumpMenu('parent',this,0)" class="InputBox">
<?php
		$c=0;
		if ($d['contains']!="")
		{
			$pbs=explode(":",$d['contains']);
			foreach($pbs as $k=>$v)
			{
				$c++;
				$e = $problems[$v] ?? null;
		?>
			<option value="../problem/problem.php?pid=<?php echo $v ?>">[<?=h($e['probname'])?>]</option>
<?php		}
	   }?>
<option selected="selected">[<?php echo $c ?>个题目]</option>
</select> </td>
    <td>
        <select name="menu2" onchange="MM_jumpMenu('parent',this,0)" class="InputBox">
		<?php
		$qRows = $rounds[$d['cbid']] ?? [];
		$c = count($qRows);
		foreach ($qRows as $j => $e) {
		?>
			<option value="comptime.php?ctid=<?php echo $e['ctid'] ?>"><?php echo date("在 Y-m-d H:i:s 的开始比赛",$e['starttime']) ?></option>
<?php }?>
<option selected="selected">[<?php echo $c ?>个场次]</option>
<option value="editcomptime.php?action=add&cbid=<?php echo $d['cbid'] ?>">[添加新场次]</option>
		</select>
	</td>
<td><a href='../user/detail.php?uid=<?=$d['ouid']?>' target='_blank'>
<?=gravatar::showImage($d['email']);?>
<?php if(Privilege::allows("查看用户")) echo h($d['realname']); else echo h($d['nickname']);?>
</a></td>
    <td><a href="editcompbase.php?action=edit&cbid=<?php echo $d['cbid'] ?>">修改比赛</a></td>
  </tr>
<?php
	}
?>
</table>
<?php  分页($cnt, ($_GET['page'] ?? 1), '?settings=comp&'); ?>
</div>
<?php
	include_once("../include/footer.php");
?>
