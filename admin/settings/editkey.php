<?php
require_once("../../include/stdhead.php");
gethead(1,"advadmin","修改参数");
include("../../include/fckeditor/fckeditor.php") ;
?>

<script type="text/javascript">
function FCKeditor_OnComplete( editorInstance )
{
	var oCombo = document.getElementById( 'cmbToolbars' ) ;
	oCombo.value = editorInstance.ToolbarSet.Name ;
	oCombo.style.visibility = '' ;
}
</script> 

<a href="../settings.php?settings=settings">参数设置</a>
<?php
$p=new DataAccess();

if ((int)$_GET['ssid'])
	$sql="select * from settings where ssid={$_GET['ssid']}";
else
	$sql="select * from settings where name='{$_GET['sname']}'";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);
$ssid=$d['ssid'];
$sname=$d['name'];

?>
<p><a href="editkey.php?sname=<?php echo $sname ?>&method=html">HTML视图</a>
<a href="editkey.php?sname=<?php echo $sname ?>&method=text">文本视图</a></p>
<form id="form1" name="form1" method="post" action="doeditkey.php?ssid=<?php echo $ssid ?>">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td>参数</td>
    <td><?php echo $sname; ?></td>
  </tr>
  <tr>
    <td>值</td>
    <td>
<?php
if ($_GET['method']=='text')
{
?>
<textarea name="value" cols="80" rows="10" class="TextArea"><?php echo $d['value'] ?></textarea>
<?php
}
else if($_GET['method']=='html')
{
	$oFCKeditor = new FCKeditor('value') ;
	$oFCKeditor->BasePath = "../../include/fckeditor/" ;
	
	if ( isset($_GET['Toolbar']) )
		$oFCKeditor->ToolbarSet = htmlspecialchars($_GET['Toolbar']);
	
	$oFCKeditor->Value =$d['value'] ;
	$oFCKeditor->Create() ;
}
?>

</td>
  </tr>
</table>
<p>
  <input type="submit" name="Submit" value="提交修改"  class="Button" />
</form>

<?php
	include_once("../../include/stdtail.php");
?>