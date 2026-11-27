<?php
// 保存评测机表单。管理员在 editgrader.php 选择启用状态、地址和优先级，然后携带令牌 POST 到此处。
require_once(__DIR__.'/../include/header.php');
gethead(0, '管理评测', '');
requirePost();

$action = $_POST['action'] ?? $_GET['action'] ?? '';   // 旧表单把动作放在 URL 中，本次请求本身仍须 POST。
$id = (int)($_POST['grid'] ?? $_GET['grid'] ?? 0);
$back = 取路径('submit/graderlist.php');   // 保存或启停后回到同一份清单查看结果。

if ($action === 'add' || $action === 'edit') {
    $address = trim($_POST['address'] ?? '');
    $enabled = (int)!empty($_POST['enabled']);   // 取消勾选时不再接受新任务。
    $priority = (int)($_POST['priority'] ?? 0);   // 自动选机先尝试高优先级。
    $memo = $_POST['memo'] ?? '';
    if ($action === 'add') Judge::create($address, $priority, $enabled, $memo);
    else Judge::edit($address, $priority, $enabled, $memo, $id);   // 编辑只修改这个机位的站点登记。
    i提示('评测机 '.h($address).' 已保存。', $back);
    return;   // 配置保存已经完成，避免继续发送启停请求。
}

if (!in_array($action, ['start', 'stop'], true)) { http_response_code(400); exit('未知评测操作。'); }   // 配置操作已经处理，余下只允许远端启停。
$start = $action === 'start';
$label = $start ? '启动' : '关闭';                                     // 页面沿用操作员看到的按钮文字。
try {
    $success = Judge::setRunning($id, $start);                         // 等到远端确认再向操作员报告成功。
} catch (OutOfBoundsException $error) {
    http_response_code(404);   // 未知编号不能获得用于签名请求的远端地址。
    exit('评测机不存在。');
}
if (!$success) i异常($label.'评测机失败。', $back);   // 掉线或拒绝操作时不能显示成功。
i提示($label.'评测机成功。', $back);
