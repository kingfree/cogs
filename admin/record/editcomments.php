<?php
require_once("../../include/stdhead.php");
gethead(1,"管理评论","修改评论");
?>

<a href="../settings.php?settings=comments"><?php echo $STR[admin][comments]; ?></a>
<p>
<?php
if ($_GET[action]=='edit')
{
	$p=new DataAccess();
	$sql="select * from comments where cid={$_GET[cid]}";
	$cnt=$p->dosql($sql);
}
if ($cnt)
{
	$d=$p->rtnrlt(0);
}
else
{
	echo '<script>document.location="../../error.php?id=12"</script>';
}
?>
<form action="doeditcomments.php" method="post">
			<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
              <tr>
                <th width="9%" valign="top" scope="col">CID</th>
                <th width="91%" scope="col"><?php echo $d[cid] ?></th>
              </tr>
              <tr>
                <td valign="top">显示代码</td>
                <td><input name="showcode" type="checkbox" id="showcode" value="1" <?php if ($d[showcode]) echo 'checked="checked"'; ?> /></td>
              </tr>
              <tr>
                <td valign="top">评论内容</td>
                <td><textarea name="detail" cols="80" rows="10" class="TextArea" id="detail"><?php echo $d[detail] ?></textarea></td>
              </tr>
            </table>
			<br>
			<input type="submit" value="提交"  class="Button">
            <input name="action" type="hidden" id="action" value="<?php echo $_GET[action] ?>" />
            <input name="cid" type="hidden" id="cid" value="<?php echo $d[cid] ?>" />
</form>

<?php
	include_once("../../include/stdtail.php");
?>
