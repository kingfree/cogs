<?php
// 帮助页，在 docs/index.php 显示本站管理员配置的使用帮助和站点介绍。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","帮助");  // 游客也能看到站点介绍和判定说明。
?>
<div class='row-fluid'>
<div class='span3'>
<div class='page'>
<?php echo 输出文本($SET['global_about']) /* 站点介绍展开管理员模板中的公开参数。 */ ?>
</div>
<div class='page'>
<a href="map.php" class="btn btn-block btn-primary"><span class='icon-map-marker icon-white'></span>站点地图</a>
</div>
<table class='table table-striped table-condensed'>
<?php foreach (str_split('APWTMECRD') as $verdict) { /* 保留帮助页原有的判定次序。 */ ?>
<tr><td><?=评测结果($verdict)?></td>
<td><?=评测信息($verdict)?></td></tr>
<?php  } ?>
</table>
</div>
<div class='span9'>
<div class='page'>
<?php echo 输出文本($SET['global_help']) /* 右侧保留本站配置的完整帮助正文。 */ ?>
</div>
</div>
<?php
    include_once(__DIR__.'/../include/footer.php');
?>
