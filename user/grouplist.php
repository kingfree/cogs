<?php
// 通过 user/grouplist.php 查看分组、父组和负责人；管理入口使用各组的 gid。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","分组列表");
?>
<div class='row-fluid'>
<?php if(Privilege::allows('分组管理')) { ?>
<a href="editgroup.php?action=add" class="btn btn-info">添加新分组</a>
<?php } ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead>
  <tr>
    <th width='160px'>分组名</th>
    <th width='160px'>上级分组</th>
    <th>备注</th>
    <th width='120px'>组管理员</th>
    <?php if(Privilege::allows('普通用户')) { ?>
    <th width='60px'>加入</th>
    <?php } ?>
    <?php if(Privilege::allows('分组管理')) { ?>
    <th class='admin' width='40px'>操作</th>
    <?php } ?>
  </tr>
</thead>
<?php
    $tree = UserGroup::tree(); // 父组名称来自同一份组树，保留父组已移除的历史分组。
    foreach (UserGroup::withManagers() as $group) {
        $parent = $tree[$group['parent']] ?? null;
?>
  <tr>
    <td><b><a href="index.php?gid=<?=$group['gid']?>"><?=h($group['gname'])?></a></b></td>
    <td><?php
if ($parent) {
?><a href="index.php?gid=<?=$parent['gid']?>"><?=h($parent['gname'])?></a><?php
}
?></td>
    <td><?=sp2n(htmlspecialchars($group['memo'])) ?></td>
    <td><a href="../user/detail.php?uid=<?=$group['uid']?>"><?=h($group['nickname'])?></a></td>
    <?php if(Privilege::allows('普通用户')) { ?>
    <td>
<form method="post" action="../mail/index.php" class='form-inline'><?=formTokenField()?>
<input name="toid" type="hidden" value=<?=$group['uid']?> />
<input name="title" type="hidden" value="申请加入：<?=h($group['gname'])?>" />
<input name="text" type="hidden" value="请输入你的加入原因，或你的个人信息，以通过组管理员的验证。" />
<button type="submit" class='btn btn-mini'>申请</button>
</form>
    </td>
    <?php } ?>
    <?php if(Privilege::allows('分组管理')) { ?>
    <td><a href="editgroup.php?action=edit&gid=<?=$group['gid'] ?>">修改</a></td>
    <?php } ?>
  </tr>
<?php
    }
?>
</table>
</div>
<?php
    include_once(__DIR__.'/../include/footer.php');
?>
