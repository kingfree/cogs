<?php
require_once("../include/header.php");
gethead(1,"参数设置","修改参数");
if($_GET['method']=='html') $LIB->editor("value");
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
<div class='container'>
<form method="post" action="doeditkey.php?ssid=<?=$ssid?>" class='form-horizontal'>
<div class='control-group'>
<div class='controls'>
<ul class='nav nav-tabs' id='view'>
<li class='<?if($_GET['method']=='html') echo "active";?>'><a href="editkey.php?sname=<?php echo $sname ?>&method=html">HTML视图</a></li>
<li class='<?if($_GET['method']=='text') echo "active";?>'><a href="editkey.php?sname=<?php echo $sname ?>&method=text">文本视图</a></li>
</ul>
</div>
</div>
<div class='control-group'>
<label class='control-label'>参数</label>
<div class='controls'>
<code><?php echo $sname; ?></code>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='value'>值</label>
<div class='controls'>
<textarea id="value" name="value" class='textarea'><?=$d['value']?></textarea>
</div>
</div>
<div class='form-actions'>
<button type="submit" class='btn btn-primary'>提交</button>
</div>
</form>
</div>
<?php
include_once("../include/footer.php");
?>
