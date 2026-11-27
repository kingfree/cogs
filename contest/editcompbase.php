<?php
// 由 contest/editcompbase.php?action=add 打开题单表单；保存交给 doeditcompbase.php，排期另在场次表单中设置。
require_once(__DIR__.'/../include/header.php');
gethead(0, '修改比赛', ''); // 在加载管理表单前核对访问权限。

$id = (int)($_GET['cbid'] ?? 0);
$contest = ['cbid'=>'', 'cname'=>'', 'contains'=>'']; // 新建题单的编号在首次保存时才分配。
if (($_GET['action'] ?? '') === 'edit') {
    $contest = Contest::byId($id)[0] ?? null; // 编辑模式加载现有名称和赛题选择。
    if (!$contest) throw new DomainException('题单不存在。', 404);
}
$selected = array_flip(Contest::problemIds($contest['contains'])); // 复选框按题号回显勾选状态。
$choices = array_column(Problem::choices(), null, 'pid'); // 以题号匹配编辑表单仍能选择的试题。
$ordered = [];
foreach ($selected as $problemId => $position) {
    if (isset($choices[$problemId])) $ordered[$problemId] = $choices[$problemId]; // 原赛题按场内顺序排在前面，单独改名时顺序不变。
}
$ordered += $choices; // 尚未选入的题目仍按题库顺序列出。
gethead(1, '', '修改比赛');
?>
<div class='row-fluid'>
<form method="post" action="doeditcompbase.php?action=<?=h($_GET['action'] ?? '')?>&cbid=<?=$id?>" class='form-inline'><?=formTokenField()?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td width='80px'>CBID</td>
    <td><?=$contest['cbid'] ?></td>
  </tr>
  <tr>
    <td>比赛名</td>
    <td>
      <input name="cname" type="text" value="<?=h($contest['cname'])?>" /></td>
  </tr>
  <tr>
    <td>关联题目</td>
    <td>
	<ul class='nav nav-pills'>
	<?php
	foreach ($ordered as $problem) {
?>
<li style='width: 200px'>
<input name="cons[<?=$problem['pid']?>]" type="checkbox" id="cons[<?=$problem['pid']?>]" value="<?=$problem['pid']?>" <?php if (isset($selected[$problem['pid']])) echo 'checked="checked"' ?> />
<label for="cons[<?=$problem['pid']?>]" class='<?php
if(isset($selected[$problem['pid']])) echo "label label-success";
else if(!$problem['submitable']) echo "label label-important";
?>'><?=$problem['pid']?>.<?=shortname($problem['probname']) ?></label>
</li>
<?php
	}
?>
</ul></td>
  </tr>
<tr><td></td><td>
<button type="submit" class='btn btn-primary'>提交修改</button>
</td></tr>
</table>
</form>
</div>
<?php
	include_once(__DIR__.'/../include/footer.php');
?>
