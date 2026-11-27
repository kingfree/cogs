<?php
// 为有分类管理权限的用户显示分类编辑表单，保存后所有采用该分类的题目共用新名称和说明。
// 打开 problem/editcate.php?action=add 新建，或用 ?action=edit&caid=1 修改。
require_once(__DIR__.'/../include/header.php');
gethead(1,"分类管理","修改分类");
?>
<div class='row-fluid'>
<?php
$action = $_GET['action'] ?? 'add';
$categoryId = (int)($_GET['caid'] ?? 0);
$category = ['cname'=>'', 'memo'=>''];                  // 新建表单留空，编辑表单取全站共用的分类。
if ($action === 'edit') {
    $category = Category::byId($categoryId)[0] ?? null;
    if (!$category) 异常('分类不存在。', 取路径('problem/catelist.php'));  // 分类被移除后，旧表单链接应返回分类列表。
}
?>
<form method="post" action="doeditcate.php?action=<?=h($action)?>&caid=<?=$categoryId?>" class='form-horizontal'><?=formTokenField()?>
<div class='control-group'>
<label class='control-label' for='caid'>CAID</label>
<div class='controls'>
<span id='caid' class='uneditable-input' ><?=$category['caid'] ?? "新建"?></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='cname'>分类名称</label>
<div class='controls'>
<input name="cname" type="text" id="cname" value="<?=h($category['cname'])?>" />
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memo'>分类说明</label>
<div class='controls'>
<textarea id='memo' name="memo" class="textarea"><?=h($category['memo'])?></textarea>
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
