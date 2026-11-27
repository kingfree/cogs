<?php
// 评测机列表。访问 submit/graderlist.php 查看机器和状态，拥有管理评测权限的账号可进入配置页。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","评测机列表");
$canManage = Privilege::allows('管理评测');                         // 普通访客可查看状态，控制表单只给管理员。
?>
<div class='row-fluid'>
<?php if ($canManage) { ?>
<a class="btn btn-info pull-left" href="editgrader.php?action=add">添加新评测机</a>
<?php } ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>GRID</th>
    <th>名称</th>
    <th>评测次数</th>
    <th>状态</th>
    <th>版本</th>
    <th>备注</th>
    <th>地址</th>
    <th>优先级</th>
<?php if ($canManage) { ?>
    <th>操作</th>
<?php } ?>
  </tr>
<?php
foreach (Judge::all() as $judge) {                                      // 停用机位也保留在管理列表。
    $state = Judge::request($judge['address'], ['action'=>'state']);     // 显示远端刚返回的状态。
?>
  <tr>
    <td><?php echo $judge['grid'] ?></td>
    <td><?=h($state['name'] ?? '无法连接')?></td>
    <td><?php echo h($state['cnt'] ?? '未知') ?></td>
    <td><?php echo h($state['state'] ?? '未知') ?></td>
    <td><?php echo h($state['ver'] ?? '未知') ?></td>
    <td><?php echo sp2n(htmlspecialchars($judge['memo'])) ?></td>
    <td><?=h($judge['address'])?></td>
    <td><?php echo $judge['priority'] ?></td>
<?php if ($canManage) { ?>
    <td>
        <a href="editgrader.php?action=edit&amp;grid=<?php echo $judge['grid'] ?>">修改</a>
        <form method="post" action="doeditgrader.php" style="display:inline"><?=formTokenField()?>
        <input type="hidden" name="grid" value="<?=(int)$judge['grid']?>">
        <button class="btn btn-link" name="action" value="start">启动</button>
        <button class="btn btn-link" name="action" value="stop">关闭</button>
        </form>
    </td>
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
