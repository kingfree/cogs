<?php
require_once("../include/header.php");
gethead(1,"参数设置","参数设置");
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th width='70px'>修改</th>
    <th width='20%'>参数</th>
    <th>值</th>
  </tr>
<?php
	$settings = Setting::all();

	foreach ($settings as $i => $d) {
?>
  <tr>
    <td>
    <a href="editkey.php?sname=<?=h($d['name'])?>&method=html">富文</a>
    <a href="editkey.php?sname=<?=h($d['name'])?>&method=text">纯文</a>
    </td>
    <td><code><?=h($d['name'])?></code></td>
    <td><?=h($d['value'])?></td>
  </tr>
<?php
	}
?>
</table>
</div>
<?php
require_once("../include/footer.php");
?>
