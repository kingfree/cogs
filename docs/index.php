<?php
require_once("../include/header.php");
gethead(1,"","帮助");
?>
<div class='row-fluid'>
<div class='span3'>
<table class='table table-striped table-condensed'>
<caption>评测结果说明</caption>
<? $s="AWTMREDCNP";
for($i=0; $i<strlen($s); $i++) {?>
<tr><td><?=评测结果($s[$i])?></td>
<td><?=评测信息($s[$i])?></td></tr>
<? } ?>
</table>
<div class='page'>
<?php echo 输出文本($SET['global_about']) ?>
</div>
</div>
<div class='span9'>
<div class='page'>
<?php echo 输出文本($SET['global_help']) ?>
</div>
</div>
<?php
    include_once("../include/footer.php");
?>
