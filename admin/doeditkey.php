<?php
// editkey.php 的表单提交配置值，保留旧表单 URL 里携带的 ssid。
// 例：在 admin/editkey.php?ssid=1 编辑该行后保存，表单会把 value 和当前会话的令牌送到这里。
require_once __DIR__.'/../include/header.php';
gethead(0, '参数设置', '');  // 只允许拥有参数设置权限的账号写入。
requirePost();  // GET 进入确认页，不会使用查询串直接更新配置。
$id = (int)($_POST['ssid'] ?? $_GET['ssid'] ?? 0);  // 优先使用提交的编号，兼容旧版放在表单 action 里的 ssid。
$value = $_POST['value'] ?? null;  // 区分字段缺失和主动清空值，后者允许保存。
if (!is_string($value) || $id < 1) { http_response_code(400); exit('参数值或编号无效。'); }  // 缺值或编号无效时返回 400，避免误写其他记录。
if (!Setting::change($value, $id)) { http_response_code(404); exit('设置不存在。'); }  // 只更新已有设置，编辑期间记录消失则返回 404。
i提示('修改参数成功！', 取路径('admin/settings.php?settings=settings'));  // 成功后返回配置清单，包括重复保存同一值的情况。
