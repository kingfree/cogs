<?php
// 管理评测机配置。例如 submit/editgrader.php?action=edit&grid=1 打开指定机器的表单。
require_once("../include/header.php");
gethead(1,"管理评测","修改评测机");
?>
<div class='container'>
<?php
if ($_GET['action']=='edit')
{
	$judges = Judge::byId($_GET['grid']);
	$cnt = count($judges);
	$d=($judges[0] ?? false);
}
?>
<form method="post" action="doeditgrader.php?action=<?=h($_GET['action'] ?? '')?>&grid=<?=h($_GET['grid'] ?? '')?>"><?=formTokenField()?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td>GRID</td>
    <td><?php echo $d['grid'] ?></td>
  </tr>
  <tr>
    <td>地址</td>
    <td>
      <input name="address" type="text" id="address" value="<?=h($d['address'])?>"></td>
  </tr>
  <tr>
    <td>优先级</td>
    <td>
      <input name="priority" type="number" id="priority" value="<?php echo (int)$d['priority'] ?>"></td>
  </tr>
  <tr>
    <td>可用</td>
    <td><input name="enabled" type="checkbox" id="enabled" value="1" <?php if ($d['enabled']) echo 'checked="checked"'; ?>></td>
  </tr>
  <tr>
    <td>备注</td>
    <td><textarea name="memo" class="textarea"><?=h($d['memo'])?></textarea></td>
  </tr>
<tr><td></td><td>
<button type="submit" class='btn btn-primary'>提交修改</button>
</td></tr>
</table>
</form>
</div>
<?php
	include_once("../include/footer.php");
?>
