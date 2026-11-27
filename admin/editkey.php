<?php
// 编辑单项设置。例如 admin/editkey.php?sname=style_pagesize 按名称打开，新值交给 doeditkey.php 保存。
require_once __DIR__.'/../include/header.php';
gethead(0, '参数设置', '');  // 先核验权限，避免暴露参数名称及原值。
$setting = Setting::find((int)($_GET['ssid'] ?? 0), $_GET['sname'] ?? '');  // 兼容按编号或按名称指向某一设置的链接。
if (!$setting) { http_response_code(404); exit('设置不存在。'); }  // 键不存在时返回 404，停止生成保存表单。
gethead(1, '参数设置', '修改参数');  // 确认已经找到设置后，才输出页面头部。
$mode = $_GET['method'] ?? 'text';  // 旧链接没有 method 时默认使用纯文本编辑。
if ($mode === 'html') renderEditor('value');  // 只为富文本模式加载编辑器，两个视图提交同一字段。
$nameForUrl = rawurlencode($setting['name']);  // 把参数名编码成查询参数，供两个视图相互切换。
?>
<div class='row-fluid'>
<form method="post" action="doeditkey.php?ssid=<?=h($setting['ssid'])?>" class='form-horizontal'><?=formTokenField()?>
<div class='control-group'>
<div class='controls'>
<ul class='nav nav-tabs' id='view'>
<li class='<?php if($mode === 'html') echo "active";?>'><a href="editkey.php?sname=<?=$nameForUrl?>&method=html">HTML视图</a></li>
<li class='<?php if($mode === 'text') echo "active";?>'><a href="editkey.php?sname=<?=$nameForUrl?>&method=text">文本视图</a></li>
</ul>
</div>
</div>
<div class='control-group'>
<label class='control-label'>参数</label>
<div class='controls'>
<code><?=h($setting['name'])?></code>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='value'>值</label>
<div class='controls'>
<textarea id="value" name="value" class='textarea'><?=h($setting['value']) /* 已有值转义后放回文本框，包含 HTML 的配置也能完整编辑。 */ ?></textarea>
</div>
</div>
<div class='form-actions'>
<button type="submit" class='btn btn-primary'>提交</button>
</div>
</form>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
