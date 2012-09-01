<?php
require_once("../include/header.php");
gethead(1,"分类管理","修改分类");
?>
<div class='row-fluid'>
<?php
if ($_GET[action]=='edit') {
	$p=new DataAccess();
	$sql="select * from category where caid={$_GET['caid']}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
}
?>
<form method="post" action="doeditcate.php?action=<?php echo $_GET[action] ?>&caid=<?php echo $_GET[caid]; ?>" class='form-horizontal'>
<div class='control-group'>
<label class='control-label' for='caid'>CAID</label>
<div class='controls'>
<span id='caid' class='uneditable-input' ><?=$d['caid'] ? $d['caid'] : "新建"?></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='cname'>分类名称</label>
<div class='controls'>
<input name="cname" type="text" id="cname" value="<?=$d['cname'] ?>" />
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memo'>分类说明</label>
<div class='controls'>
<textarea id='memo' name="memo" class="textarea"><?=$d['memo']?></textarea>
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
