<?php
// 文章目录。访问 page/index.php?key=训练 搜索当前会话可阅读的文章，然后对筛选结果分页。
require_once("../include/header.php");
gethead(1,"","页面列表");
?>
<div class='row-fluid'>
<?php if(Privilege::allows('修改页面')) { ?>
<a href="editpage.php?action=add" class="btn btn-info pull-left">添加新页面</a>
<?php } ?>

<?php
$pages = Page::search($_GET['key'] ?? '');
$cnt = count($pages);
$st=检测页面($cnt, ($_GET['page'] ?? 1));
?>
<form method="get" action="" class='form-search center'>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=h($_GET['key'] ?? '')?>' placeholder='搜索页面'/>
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
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=($pages[$i] ?? false);
    if($_GET['key'] && $cnt == 1)
        echo "<script language='javascript'>location='page.php?aid={$d['aid']}';</script>";
?>
<tr>
<td><?php echo $d['aid'] ?></td>
<td align=left>
<b><a href="page.php?aid=<?=$d['aid'] ?>"><?=h($d['title'])?></b>
<?php if(Privilege::allows('修改页面')) { ?>
<a href="editpage.php?action=edit&aid=<?=$d['aid']?>" title="修改页面 <?=h($d['title'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</td>
<td><?=date('Y-m-d', $d['time']) ?></td>
<td><?=date('Y-m-d', $d['etime']) ?></td>
<td><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=h($d['gname'])?></a></td>
<td><a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'>
<?=gravatar::showImage($d['email']);?>
<?php if(Privilege::allows("查看用户")) echo h($d['realname']); else echo h($d['nickname']);?>
</a></td>
</tr>
<?php } ?>
</table>
<?php  分页($cnt, ($_GET['page'] ?? 1), '?key='.$_GET['key'].'&'); ?>
</div>

<?php
include_once("../include/footer.php");
?>
