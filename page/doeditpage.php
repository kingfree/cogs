<?php
// 处理文章编辑表单。POST action=edit、aid、正文和会话令牌修改现有文章；写入权限在分派操作前检查。
require_once(__DIR__.'/../include/header.php');
gethead(8, "修改页面", "");
requirePost();
$action = ($_POST['action'] ?? $_GET['action'] ?? ''); // 保留旧操作链接带来的 action，POST 字段优先。
$aid = (int)($_POST['aid'] ?? $_GET['aid'] ?? 0);
if ($action === 'del') {
    Page::remove($aid); // 只有已通过修改页面权限和表单令牌校验的请求能执行删除。
    提示("删除页面成功", 取路径("page/index.php"));
    return; // 删除后返回目录，不再读取编辑表单里的标题和正文。
}
if ($action !== 'edit' && $action !== 'add') 异常("未知页面操作");
try {
    $page = Page::fields($_POST); // 新建和编辑使用同一标题、正文和开放范围校验。
} catch (InvalidArgumentException $error) {
    异常($error->getMessage()); // 输入问题可由编辑者改正，底层数据库故障另行上报。
}
if ($action === 'edit') {
    Page::edit($aid, $page); // 编辑沿用原先作者与创建时间。
} else {
    $aid = Page::create((int)$_SESSION['ID'], $page); // 新文章使用当前管理员作为作者，提示页需要它的编号。
}
提示('页面已保存', 取路径("page/page.php?aid=$aid"));
