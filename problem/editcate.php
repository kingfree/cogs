<?php
require_once("../include/header.php");
gethead(1,"分类管理","修改分类");
?>
<div class='row-fluid'>
<?php
if ($_GET['action']=='edit') {
	$categories = Category::byId($_GET['caid']);
	$cnt = count($categories);
	$d=($categories[0] ?? false);
}
?>
<form method="post" action="doeditcate.php?action=<?=h($_GET['action'] ?? '')?>&caid=<?=h($_GET['caid'] ?? '')?>" class='form-horizontal'><?=formTokenField()?>
<div class='control-group'>
<label class='control-label' for='caid'>CAID</label>
<div class='controls'>
<span id='caid' class='uneditable-input' ><?=$d['caid'] ? $d['caid'] : "新建"?></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='cname'>分类名称</label>
<div class='controls'>
<input name="cname" type="text" id="cname" value="<?=h($d['cname'])?>" />
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memo'>分类说明</label>
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
