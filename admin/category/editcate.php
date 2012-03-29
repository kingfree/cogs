<?php
require_once("../../include/stdhead.php");
gethead(1,"admin","修改分类");
?>

<?php
if ($_GET[action]=='edit')
{
	$p=new DataAccess();
	$sql="select * from category where caid={$_GET[caid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
}
?>
<form id="form1" name="form1" method="post" action="doeditcate.php?action=<?php echo $_GET[action] ?>&caid=<?php echo $_GET[caid]; ?>">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td>CAID</td>
    <td><?php echo $d[caid] ?></td>
  </tr>
  <tr>
    <td>组名</td>
    <td>
      <input name="cname" type="text" class="InputBox" id="cname" value="<?php echo $d[cname] ?>" /></td>
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