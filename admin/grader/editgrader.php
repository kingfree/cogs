<?php
require_once("../../include/stdhead.php");
gethead(1,"管理评测","修改评测机");
?>
<div class='container'>
<?php
if ($_GET[action]=='edit')
{
	$p=new DataAccess();
	$sql="select * from grader where grid={$_GET[grid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
}
?>
<form method="post" action="doeditgrader.php?action=<?php echo $_GET[action] ?>&grid=<?php echo $_GET[grid]; ?>">
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td>GRID</td>
    <td><?php echo $d['grid'] ?></td>
  </tr>
  <tr>
    <td>地址</td>
    <td>
      <input name="address" type="text" class="InputBox" id="address" value="<?php echo $d['address'] ?>" size="40" /></td>
  </tr>
  <tr>
    <td>优先级</td>
    <td>
      <input name="priority" type="number" class="InputBox" id="priority" value="<?php echo $d['priority'] ?>" size="40" /></td>
  </tr>
  <tr>
    <td>可用</td>
    <td><input name="enabled" type="checkbox" id="enabled" value="1" <?php if ($d['enabled']) echo 'checked="checked"'; ?> class="InputBox" /></td>
  </tr>
  <tr>
    <td>备注</td>
    <td><textarea name="memo" class="textarea"><?php echo $d[memo] ?></textarea></td>
  </tr>
<tr><td></td><td>
<button type="submit" class='btn btn-primary'>提交修改</button>
</td></tr>
</table>
</form>
</div>
<?php
	include_once("../../include/stdtail.php");
?>
