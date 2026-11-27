<?php
// 浏览全站分类和说明，具有分类管理权限的账号还能打开编辑表单。
// 打开 problem/catelist.php，点击分类名可进入对应的题目列表。
require_once("../include/header.php");
gethead(1,"","分类列表");
?>
<div class='row-fluid'>
<?php if(Privilege::allows('分类管理')) { ?>
<a href="editcate.php?action=add" class="btn btn-info">添加新分类</a>
<?php } ?>
<?php
    $categories = Category::all();
    $cnt = count($categories);
?>
<table id="catelist" class='table table-striped table-condensed table-bordered fiexd'>
<thead>
  <tr>
    <th>caid</th>
    <th>分类</th>
    <?php if(Privilege::allows('分类管理')) { ?>
    <th class='admin' width='40px'>操作</th>
    <?php } ?>
    <th>备注</th>
  </tr>
</thead>
<?php
for ($i=$st;$i<$cnt;$i++) {
    $d=($categories[$i] ?? false);
?>
  <tr>
    <td><?=$d['caid']?></td>
    <td><a href="index.php?caid=<?php echo $d['caid'] ?>"><?=h($d['cname'])?></a></td>
    <?php if(Privilege::allows('分类管理')) { ?>
    <td><a href="editcate.php?action=edit&caid=<?php echo $d['caid'] ?>">修改</a></td><?php } ?>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
  </tr>
<?php
    }
?>
</table>
</div>
<?php
    include_once("../include/footer.php");
?>
