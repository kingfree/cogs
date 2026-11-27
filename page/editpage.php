<?php
// 管理员从 page/editpage.php?action=edit&aid=1 打开文章表单，在此选择可阅读的分组和等级。
require_once(__DIR__.'/../include/header.php');
gethead(1,"修改页面","修改页面");
$action = $_GET['action'] ?? '';
$id = (int)($_GET['aid'] ?? 0); // 删除确认和编辑表单都只传规范的整数文章编号。
if ($action === 'del') {
    // 旧链接仍显示确认页，带令牌的 POST 才会执行删除。
?>
<form method="post" action="doeditpage.php?action=del&amp;aid=<?=$id?>">
<?=formTokenField()?>确认删除该页面？ <button type="submit">确认删除</button>
</form>
<?php return; }
if ($action === 'edit') {
    $article = Page::forEditor($id); // 管理员可编辑尚未向其所在分组公开的文章。
    if (!$article) 异常('页面不存在。', 取路径('page/index.php'));
} elseif ($action === 'add') {
    $article = ['aid'=>'', 'title'=>'', 'force'=>0, 'group'=>0, 'text'=>'']; // 默认公开；表单沿用已发布的字段名。
} else {
    异常('未知页面操作');
}
renderEditor('detail');
$groups = UserGroup::all(); // 开放分组选择保存 gid，重命名分组不会改变文章归属。
?>
<div class='row-fluid'>
<form action="doeditpage.php" method="post" class='form-inline' id="tijiao"><?=formTokenField()?>
<dl class='dl-horizontal'>
<dt>AID</dt>
<dd><?=$article['aid'] ?>
<input name="aid" type="hidden" id="aid" value="<?php echo $article['aid'] ?>" />
</dd>
<dt>页面标题</dt>
<dd><input name="title" type="text" id="title" value="<?=h($article['title'])?>"></dd>
<dt>阅读权限</dt>
<dd><input name="force" type="number" id="force" value="<?=$article['force'] ?>" /> </dd>
<dt>开放分组</dt>
<dd><select name="group" id="group">
<?php
foreach ($groups as $group) {
?>
<option value="<?php echo $group['gid'] ?>" <?php if($group['gid']==$article['group']) echo 'selected="selected"' ?>>[<?=h($group['gname'])?>]</option>
<?php } ?>
</select></dd>
<dt>页面内容</dt>
<dd>
<textarea id="detail" name="text" class='pagearea'><?=h($article['text'])?></textarea>
</dd>
<dd><button type="submit" class='btn btn-primary'>单击此处提交对该页面的修改</button>
<input name="action" type="hidden" id="action" value="<?=h($action)?>" />
</dd>
</dl>
</form>
</div>

<?php
include_once(__DIR__.'/../include/footer.php');
?>

