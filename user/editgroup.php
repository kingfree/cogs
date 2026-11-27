<?php
// 通过 user/editgroup.php?action=edit&gid=1 查看目标组的名称、说明、负责人和父组，提交给 doeditgroup.php。
require_once(__DIR__.'/../include/header.php');
gethead(1,"分组管理","修改分组");
$group = ['gid'=>0, 'adminuid'=>$_SESSION['ID'], 'parent'=>1, 'gname'=>'', 'memo'=>'']; // 新组默认属于注册用户组。
$action = $_GET['action'] ?? 'add'; // 区分新建默认值与已有分组的设置。
if ($action === 'edit') {
    $group = UserGroup::tree()[(int)($_GET['gid'] ?? 0)] ?? null; // 编辑时展示已有的负责人和父组。
    if (!$group) 异常('无此分组！', 取路径('user/grouplist.php'));
}
?>
<div class='container'>
<form method="post" action="doeditgroup.php?action=<?=h($_GET['action'] ?? '')?>&gid=<?=h($_GET['gid'] ?? '')?>" class='form-horizontal'><?=formTokenField()?>
<div class='control-group'>
<label class='control-label' for='gid'>GID</label>
<div class='controls'>
<span id='gid' class='uneditable-input' ><?=$group['gid'] ? $group['gid'] : "新建"?></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='adminuid'>管理员uid</label>
<div class='controls'>
<input name="adminuid" type="text" id="adminuid" value="<?php echo $group['adminuid'] ?>">
</div>
</div>
<div class='control-group'>
<label class='control-label' for='gname'>分组名称</label>
<div class='controls'>
<input name="gname" type="text" id="gname" value="<?=h($group['gname'])?>" />
</div>
</div>
<div class='control-group'>
<label class='control-label' for='parent'>上级分组</label>
<div class='controls'>
<select name="parent" id="parent">
<?php
foreach (UserGroup::all() as $parent) {
    $selected = $group['parent'] == $parent['gid'] ? 'selected' : ''; // 编辑时显示原父组，提交字段保持旧的编号格式。
    echo "<option value='{$parent['gid']}' $selected>".h($parent['gname']).'</option>';
}
?>
</select>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memo'>分组说明</label>
<div class='controls'>
<textarea id='memo' name="memo" class="textarea"><?=h($group['memo'])?></textarea>
</div>
</div>
<div class='form-actions'>
<button type="submit" class='btn btn-primary'>提交</button>
</div>
</form>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
