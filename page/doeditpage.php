<?php
require_once("../include/header.php");
gethead(8, "修改页面", "");
$db = new DataAccess();
$action = $_REQUEST['action'];
$aid = isset($_REQUEST['aid']) ? (int)$_REQUEST['aid'] : 0;
if ($action === 'del') {
    $db->dosql("delete from page where aid = ?", array($aid));
    提示("删除页面成功", 取路径("page/index.php"));
    return;
}
if ($action !== 'edit' && $action !== 'add') 异常("未知页面操作");
if (!isset($_POST['title'], $_POST['text'], $_POST['force'], $_POST['group']) ||
    !is_string($_POST['title']) || !is_string($_POST['text'])) 异常("页面内容无效");
$fields = array($_POST['title'], (int)$_POST['force'], $_POST['text'], (int)$_POST['group'], time());
if ($action === 'edit') {
    $fields[] = $aid;
    $db->dosql("update page set title=?, `force`=?, `text`=?, `group`=?, etime=? where aid=?", $fields);
} else {
    $fields[] = time();
    $fields[] = (int)$_SESSION['ID'];
    $db->dosql("insert into page(title, `force`, `text`, `group`, etime, `time`, uid) values(?,?,?,?,?,?,?)", $fields);
    $aid = (int)DataAccess::connection()->lastInsertId();
}
提示("页面已保存", 取路径("page/page.php?aid=$aid"));
