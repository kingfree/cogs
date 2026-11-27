<?php
// 管理评测机配置。例如 submit/editgrader.php?action=edit&grid=1 打开指定机器的表单。
require_once(__DIR__.'/../include/header.php');
gethead(0, "管理评测", "");
$action = $_GET['action'] ?? 'add';
$grid = (int)($_GET['grid'] ?? 0);                                    // 编辑时取数据库登记值，新增时显示空表单。
$judge = ['grid'=>'', 'address'=>'', 'priority'=>0, 'enabled'=>0, 'memo'=>''];
if ($action === 'edit') {
    $judge = Judge::byId($grid);   // 编辑旧机位用当前配置预填；被删除的机位返回 404。
    if (!$judge) { http_response_code(404); exit('评测机不存在。'); }   // 未知编号不能当成新增配置保存。
}
gethead(1, "管理评测", "修改评测机");                           // 在机位存在性检查之后才发送页面内容。
?>
<div class='container'>
<form method="post" action="doeditgrader.php?action=<?=h($action)?>&amp;grid=<?=$grid?>"><?=formTokenField()?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td>GRID</td>
    <td><?php echo $judge['grid'] ?></td>
  </tr>
  <tr>
    <td>地址</td>
    <td>
      <input name="address" type="text" id="address" value="<?=h($judge['address'])?>"></td>
  </tr>
  <tr>
    <td>优先级</td>
    <td>
      <input name="priority" type="number" id="priority" value="<?php echo (int)$judge['priority'] ?>"></td>
  </tr>
  <tr>
    <td>可用</td>
    <td><input name="enabled" type="checkbox" id="enabled" value="1" <?php if ($judge['enabled']) echo 'checked="checked"'; ?>></td>
  </tr>
  <tr>
    <td>备注</td>
    <td><textarea name="memo" class="textarea"><?=h($judge['memo'])?></textarea></td>
  </tr>
<tr><td></td><td>
<button type="submit" class='btn btn-primary'>提交修改</button>
</td></tr>
</table>
</form>
</div>
<?php
	include_once(__DIR__.'/../include/footer.php');
?>
