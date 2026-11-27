<?php
// 文章目录。访问 page/index.php?key=训练 搜索当前会话可阅读的文章，然后对筛选结果分页。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","页面列表");
$keyword = $_GET['key'] ?? '';
$pageNumber = (int)($_GET['page'] ?? 1); // 列表链接从页码 1 开始。
$articles = Page::search($keyword); // 搜索、跳转和分页都只根据可读文章计算。
$total = count($articles);
$offset = 检测页面($total, $pageNumber); // 先按阅读范围与关键词筛选，然后计算本页。
$canEdit = Privilege::allows('修改页面');
$authorField = Privilege::allows('查看用户') ? 'realname' : 'nickname'; // 只有可查看用户资料的人可以在目录中看到作者真实姓名。
?>
<div class='row-fluid'>
<?php if ($canEdit) { ?>
<a href="editpage.php?action=add" class="btn btn-info pull-left">添加新页面</a>
<?php } ?>

<form method="get" action="" class='form-search center'>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=h($keyword)?>' placeholder='搜索页面'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
</div>
</form>
<table id="pagelist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th>AID</th>
<th min-width=50%>页面标题</th>
<th>创建时间</th>
<th>修改时间</th>
<th>开放分组</th>
<th>作者</th>
</tr></thead>
<?php
if ($keyword !== '' && $total === 1) {
    echo '<script>location="page.php?aid='.(int)$articles[0]['aid'].'";</script>';
}
foreach (array_slice($articles, $offset, (int)$SET['style_pagesize']) as $article) {
?>
<tr>
<td><?php echo $article['aid'] ?></td>
<td align=left>
<b><a href="page.php?aid=<?=$article['aid'] ?>"><?=h($article['title'])?></a></b>
<?php if ($canEdit) { ?>
<a href="editpage.php?action=edit&aid=<?=$article['aid']?>" title="修改页面 <?=h($article['title'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</td>
<td><?=date('Y-m-d', $article['time']) ?></td>
<td><?=date('Y-m-d', $article['etime']) ?></td>
<td><a href="../user/index.php?gid=<?=$article['gid'] ?>" target="_blank"><?=h($article['gname'])?></a></td>
<td><a href='../user/detail.php?uid=<?=$article['uid']?>' target='_blank'>
<?=gravatar::showImage($article['email']);?>
<?=h($article[$authorField])?>
</a></td>
</tr>
<?php } ?>
</table>
<?php  分页($total, $pageNumber, '?'.http_build_query(['key'=>$keyword]).'&'); ?>
</div>

<?php
include_once(__DIR__.'/../include/footer.php');
?>
