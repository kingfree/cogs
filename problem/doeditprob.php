<?php
// 保存题目、标签和上传的数据包。普通用户可投稿，启用题目和上传特殊判题器需要修改题目权限。
// 从 problem/editprob.php?action=edit&pid=1 提交表单；变更开放状态使用 action=change。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
// 旧链接上的题号和动作仍然有效，实际写入只能由已校验令牌的 POST 触发。
requirePost();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
// 两个旧入口分别把题号放在查询参数和表单正文。
$id = (int)($_GET['pid'] ?? $_POST['pid'] ?? 0);
try {
    if ($action === 'change') {
        // 开放状态单独变更，避免把列表页的操作当作空题面覆盖。
        Problem::changeAvailability($id);
        i提示('已更改题目可用状态。', 取路径('problem/index.php'));
        exit;
    }
    // 除了列表上的启停，后台只接收新增与编辑两种表单。
    if ($action !== 'add' && $action !== 'edit') {
        throw new DomainException('题目操作无效。', 400);
    }
    // 编辑必须指定已有题号，新建由数据库分配编号。
    if ($action === 'edit' && !$id) throw new DomainException('没有修改这道题的权限。', 403);
    // 将经过同一次提交的题面、分类选择和可选数据包一起保存。
    $id = Problem::save($action === 'add' ? 0 : $id, $_POST, $_FILES['datafile'] ?? []);
} catch (DomainException $error) {
    // 权限、校验和重名分别沿用业务层的 HTTP 状态。
    http_response_code($error->getCode() ?: 400);
    exit(h($error->getMessage()));
}
i提示('题目已保存。', 取路径('problem/problem.php?pid='.$id));
