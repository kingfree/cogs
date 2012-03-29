<?php
require_once("../../include/stdhead.php");
gethead(1,"admin","修改用户组");
?>

<a href="../settings.php?settings=grouplist"><?php echo $STR[admin][group]; ?></a>
<?php
$q=new DataAccess();
$d['adminuid']=$_SESSION['ID'];
$d['parent']=1;

if ($_GET[action]=='edit')
{
	$p=new DataAccess();
	$sql="select * from groups where gid={$_GET[gid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
}
?>
<form id="form1" name="form1" method="post" action="doeditgroup.php?action=<?php echo $_GET[action] ?>&gid=<?php echo $_GET[gid]; ?>">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td>GID</td>
    <td><?php echo $d[gid] ?></td>
  </tr>
  <tr>
    <td>组名</td>
    <td>
      <input name="gname" type="text" class="InputBox" id="gname" value="<?php echo $d[gname] ?>" /></td>
  </tr>
  <tr>
    <td>上级分组</td>
    <td><select name="parent" id="parent" class="InputBox">
<?php
		$sql="select * from groups order by gname";
		$c=$q->dosql($sql);
		for ($j=0;$j<$c;$j++)
		{
			$e=$q->rtnrlt($j);
?>
      <option value="<?php echo $e['gid'] ?>" <?php if($e['gid']==$d['parent']) echo 'selected="selected"' ?>>[<?php echo $e['gname'] ?>]</option>
      <?php }?>
    </select></td>
  </tr>
  <tr>
    <td>管理员uid</td>
    <td><input name="adminuid" type="text" class="InputBox" id="adminuid" value="<?php echo $d['adminuid'] ?>" /></td>
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