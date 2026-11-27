<?php
// 管理员从 contest/compbase.php 管理可复用题单；页面按题单列出每次开赛的场次。
require_once(__DIR__.'/../include/header.php');
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
$contests = Contest::withOrganizers(); // 题单列表同时显示组织者姓名。
$problems = array_column(Problem::choices(), null, 'pid'); // 按题号取名，免得每个下拉项各查一次。
$rounds = ContestRound::byContest(); // 一个题单下可以挂多场开赛记录。

$total = count($contests);
$offset=检测页面($total, ($_GET['page'] ?? 1)); // 每页按题单分页，同一题单的场次始终放在一起。
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
for ($i=$offset;$i<$total && $i<$offset+$SET['style_pagesize'] ;$i++) {
		$contest=($contests[$i] ?? false);
?>
  <tr>
    <td><?php echo $contest['cbid'] ?></td>
    <td><?=h($contest['cname'])?></td>
    <td>
        <select name="menu1" onchange="MM_jumpMenu('parent',this,0)" class="InputBox">
<?php
        $problemIds = Contest::problemIds($contest['contains']); // 编辑顺序就是下拉项目的顺序。
        foreach ($problemIds as $problemId) {
            if (!isset($problems[$problemId])) continue; // 历史题单可能还留着已删除题目的编号。
?>
            <option value="../problem/problem.php?pid=<?=$problemId?>">[<?=h($problems[$problemId]['probname'])?>]</option>
<?php } ?>
<option selected="selected">[<?=count($problemIds)?>个题目]</option>
</select> </td>
    <td>
        <select name="menu2" onchange="MM_jumpMenu('parent',this,0)" class="InputBox">
		<?php
		$schedules = $rounds[$contest['cbid']] ?? []; // 题单可以建好后再安排场次。
        foreach ($schedules as $round) {
		?>
			<option value="comptime.php?ctid=<?php echo $round['ctid'] ?>"><?php echo date("在 Y-m-d H:i:s 的开始比赛",$round['starttime']) ?></option>
<?php }?>
<option selected="selected">[<?=count($schedules)?>个场次]</option>
<option value="editcomptime.php?action=add&cbid=<?php echo $contest['cbid'] ?>">[添加新场次]</option>
		</select>
	</td>
<td><a href='../user/detail.php?uid=<?=$contest['ouid']?>' target='_blank'>
<?=gravatar::showImage($contest['email']);?>
<?php if(Privilege::allows("查看用户")) echo h($contest['realname']); else echo h($contest['nickname']);?>
</a></td>
    <td><a href="editcompbase.php?action=edit&cbid=<?php echo $contest['cbid'] ?>">修改比赛</a></td>
  </tr>
<?php
	}
?>
</table>
<?php  分页($total, ($_GET['page'] ?? 1), '?settings=comp&'); ?>
</div>
<?php
	include_once(__DIR__.'/../include/footer.php');
?>
