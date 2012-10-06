<?php
require_once("../include/header.php");
gethead(1,"","站点地图");
?>
<div class='row-fluid'>
<div class='span12'>
<div class='page'>
<?php echo 输出文本($SET['global_map']) ?>
</div>
</div>
<?php
    include_once("../include/footer.php");
?>
