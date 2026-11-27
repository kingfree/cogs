<?php
// 列出数据库中的站点参数。访问 admin/settings.php 查看名称和值，点击参数再编辑。
require_once(__DIR__.'/../include/header.php');
gethead(1, '参数设置', '参数设置');  // 配置值可能含站点内部信息，读取前检查参数设置权限。
$settings = Setting::all();  // 按配置编号顺序加载清单，两个编辑视图共用同一数据。
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th width='70px'>修改</th>
    <th width='20%'>参数</th>
    <th>值</th>
  </tr>
<?php foreach ($settings as $setting) { /* 每项设置生成一行，并以名称作为编辑链接的查询字段。 */ ?>
  <tr>
    <td>
    <a href="editkey.php?sname=<?=rawurlencode($setting['name'])?>&amp;method=html">富文</a>
    <a href="editkey.php?sname=<?=rawurlencode($setting['name'])?>&amp;method=text">纯文</a>
    </td>
    <td><code><?=h($setting['name'])?></code></td>
    <td><?=h($setting['value']) /* 在清单里只显示配置原文，HTML 值也不在这里执行。 */ ?></td>
  </tr>
<?php
	}
?>
</table>
</div>
<?php
require_once(__DIR__.'/../include/footer.php');
?>
