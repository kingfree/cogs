<?php
require_once("../../include/stdhead.php");
gethead(1,"advadmin","修改参数");
$LIB->editor("editor_id");
?>

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
<table>
  <tr>
    <th width="60px">参数</th>
    <td><b><?php echo $sname; ?></b></td>
  </tr>
  <tr>
    <th>值</th>
    <td>
<?php if ($_GET['method']=='text') { ?>
<textarea name="value" style="width:90%; height:200px;"><?php echo $d['value'] ?></textarea>
<?php } else if($_GET['method']=='html') { ?>
<textarea id="editor_id" name="value" style="width:90%; height:200px;"><?=$d['value']?></textarea>
<?php } ?>

</td>
  </tr>
</table>
  <input type="submit" name="Submit" value="提交修改"  class="Button" />
</form>

<?php
	include_once("../../include/stdtail.php");
?>
