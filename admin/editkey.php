<?php
require_once("../include/header.php");
gethead(1,"advadmin","修改参数");
$LIB->editor("editor_id");
?>

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
<ul class='nav nav-tabs' id='view'>
<li class='<?if($_GET['method']=='html') echo "active";?>'><a href="editkey.php?sname=<?php echo $sname ?>&method=html">HTML视图</a></li>
<li class='<?if($_GET['method']=='text') echo "active";?>'><a href="editkey.php?sname=<?php echo $sname ?>&method=text">文本视图</a></li>
</ul>
<form method="post" action="doeditkey.php?ssid=<?=$ssid?>" class='form-inline'>
<table>
<tr>
<th width="60px">参数</th>
<td><code><?php echo $sname; ?></code></td>
</tr>
<tr>
<th>值</th>
<td>
<?php if ($_GET['method']=='text') { ?>
<textarea name="value" class='span10'><?php echo $d['value'] ?></textarea>
<?php } else if($_GET['method']=='html') { ?>
<textarea id="editor_id" name="value" class='span10'><?=$d['value']?></textarea>
<?php } ?>
</td>
</tr>
<tr><td></td><td>
<button type="submit" class='btn btn-primary'>提交修改</button>
</td></tr>
</table>
</form>

<?php
include_once("../include/footer.php");
?>
