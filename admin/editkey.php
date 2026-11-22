<?php
require_once("../include/header.php");
gethead(1,"参数设置","修改参数");
if($_GET['method']=='html') renderEditor("value");

if ((int)$_GET['ssid'])
	$settings = Setting::byId($_GET['ssid']);
else
	$settings = Setting::byName($_GET['sname']);
$cnt = count($settings);
$d=($settings[0] ?? false);
$ssid=$d['ssid'];
$sname=$d['name'];

?>
<div class='row-fluid'>
<form method="post" action="doeditkey.php?ssid=<?=$ssid?>" class='form-horizontal'><?=formTokenField()?>
<div class='control-group'>
<div class='controls'>
<ul class='nav nav-tabs' id='view'>
<li class='<?php if($_GET['method']=='html') echo "active";?>'><a href="editkey.php?sname=<?php echo $sname ?>&method=html">HTML视图</a></li>
<li class='<?php if($_GET['method']=='text') echo "active";?>'><a href="editkey.php?sname=<?php echo $sname ?>&method=text">文本视图</a></li>
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
<textarea id="value" name="value" class='textarea'><?=h($d['value'])?></textarea>
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
