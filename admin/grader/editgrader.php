<?php
require_once("../../include/stdhead.php");
gethead(1,"admin","修改评测机");
?>

<a href="../settings.php?settings=grader">评测机管理</a>
<?php
if ($_GET[action]=='edit')
{
	$p=new DataAccess();
	$sql="select * from grader where grid={$_GET[grid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
}
?>
<form id="form1" name="form1" method="post" action="doeditgrader.php?action=<?php echo $_GET[action] ?>&grid=<?php echo $_GET[grid]; ?>">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
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
      <input name="priority" type="text" class="InputBox" id="priority" value="<?php echo $d['priority'] ?>" size="40" /></td>
  </tr>
  <tr>
    <td>可用</td>
    <td><input name="enabled" type="checkbox" id="enabled" value="1" <?php if ($d['enabled']) echo 'checked="checked"'; ?> class="InputBox" /></td>
  </tr>
  <tr>
    <td>备注</td>
    <td><textarea name="memo" cols="80" rows="10" class="TextArea"><?php echo $d[memo] ?></textarea></td>
  </tr>
</table>
<p>
  <input type="submit" name="Submit" value="提交修改"  class="Button" />
</form>

<?php
	include_once("../../include/stdtail.php");
?>