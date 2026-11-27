<?php
// 保存评测机表单。管理员在 editgrader.php 选择启用状态、地址和优先级，然后携带令牌 POST 到此处。
require_once("../include/header.php");
gethead(0, '管理评测', '');
requirePost();

require_once __DIR__.'/../include/socket.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = (int)($_POST['grid'] ?? $_GET['grid'] ?? 0);
$back = 取路径('submit/graderlist.php');

if ($action === 'add' || $action === 'edit') {
    $address = trim($_POST['address'] ?? '');
    $enabled = (int)!empty($_POST['enabled']);
    $priority = (int)($_POST['priority'] ?? 0);
    $memo = $_POST['memo'] ?? '';
    if ($action === 'add') Judge::create($address, $priority, $enabled, $memo);
    else Judge::edit($address, $priority, $enabled, $memo, $id);
    i提示('评测机 '.h($address).' 已保存。', $back);
    return;
}

if (!in_array($action, ['start', 'stop'], true)) { http_response_code(400); exit('未知评测操作。'); }
$judge = Judge::address($id)[0] ?? null;
if (!$judge) { http_response_code(404); exit('评测机不存在。'); }

// 后台的 stop 对应协议的 shutdown；必须读远端回执才能报告操作成功。
$request = ['action'=>$action === 'start' ? 'start' : 'shutdown'];
$reply = array_decode(httpsocket($judge['address'], $request));
$label = $action === 'start' ? '启动' : '关闭';
if (($reply['state'] ?? '') !== 'successful') i异常($label.'评测机失败。', $back);
i提示($label.'评测机成功。', $back);
