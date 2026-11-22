<?php
require_once("../include/header.php");
if (!Privilege::allows('修改题目')) {
    http_response_code(403);
    exit;
}

$ids = $_POST['in'] ?? '';
if ($ids !== '') {
    if (!is_string($ids) || !preg_match('/^[0-9]+(?:,[0-9]+)*$/D', $ids)) 异常("题号列表无效");
    $params = explode(',', $ids);
    $rows = Problem::selected($params);
} else {
    $rows = Problem::between((int)($_POST['start'] ?? 0), (int)($_POST['end'] ?? 0));
}

header('Content-Type: application/xml; charset=UTF-8');
if (($_POST['submit'] ?? '') !== 'Export') header('Content-Disposition: attachment; filename="cogs-fps.xml"');
Fps::export($rows);
