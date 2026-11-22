<?php
require_once("../include/header.php");
gethead(1,"分组管理","修改分组");
$d['adminuid']=$_SESSION['ID'];
$d['parent']=1;
if ($_GET['action']=='edit') {
    $groups = UserGroup::byId($_GET['gid']);
    $cnt = count($groups);
    $d=($groups[0] ?? false);
}
?>
<div class='container'>
<form method="post" action="doeditgroup.php?action=<?=h($_GET['action'] ?? '')?>&gid=<?=h($_GET['gid'] ?? '')?>" class='form-horizontal'><?=formTokenField()?>
<div class='control-group'>
<label class='control-label' for='gid'>GID</label>
<div class='controls'>
<span id='gid' class='uneditable-input' ><?=$d['gid'] ? $d['gid'] : "新建"?></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='adminuid'>管理员uid</label>
<div class='controls'>
<input name="adminuid" type="text" id="adminuid" value="<?php echo $d['adminuid'] ?>">
</div>
</div>
<div class='control-group'>
<label class='control-label' for='gname'>分组名称</label>
<div class='controls'>
<input name="gname" type="text" id="gname" value="<?=h($d['gname'])?>" />
</div>
</div>
<div class='control-group'>
<label class='control-label' for='parent'>上级分组</label>
<div class='controls'>
<select name="parent" id="parent">
<?php
$qRows = UserGroup::all();
$c = count($qRows);
foreach ($qRows as $j => $e) {
    echo "<option value='{$e['gid']}' ";
    if($d['parent']==$e['gid']) echo "selected='selected'";
    echo " >".h($e['gname'])."</option>";
}
?>
</select>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memo'>分组说明</label>
<div class='controls'>
<textarea id='memo' name="memo" class="textarea"><?=h($d['memo'])?></textarea>
</div>
</div>
<div class='form-actions'>
<button type="submit" class='btn btn-primary'>提交</button>
</div>
</form>
</div>
<?php
include_once("../include/footer.php");
?>
