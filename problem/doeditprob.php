<?php
// 保存题目、标签和上传的数据包。普通用户可投稿，启用题目和上传特殊判题器需要修改题目权限。
// 从 problem/editprob.php?action=edit&pid=1 提交表单；变更开放状态使用 action=change。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = (int)($_GET['pid'] ?? $_POST['pid'] ?? 0);
$userId = (int)$_SESSION['ID'];
$editor = Privilege::allows('修改题目');
$problem = $id ? (Problem::byId($id)[0] ?? null) : null;
if ($action !== 'add' && (!$problem || (!$editor && $userId !== (int)$problem['addid']))) {
    http_response_code(403);
    exit('没有修改这道题的权限。');
}
if ($action === 'change') {
    if (!$editor) { http_response_code(403); exit; }
    Problem::toggle($id);
    i提示('已更改题目可用状态。', 取路径('problem/index.php'));
    exit;
}
if (!in_array($action, ['add','edit'], true)) { http_response_code(400); exit; }
$name = $_POST['filename'] ?? '';
$title = trim($_POST['probname'] ?? '');
$cases = (int)($_POST['datacnt'] ?? 0);
$time = (int)($_POST['timelimit'] ?? 0);
$memory = (int)($_POST['memorylimit'] ?? 0);
$checker = (int)($_POST['plugin'] ?? 1);
if (!TestData::name($name) || strlen($name)>24 || $title === '' || mb_strlen($title)>50 || $cases<0 || $cases>4096 ||
    $time<1 || $time>30000 || $memory<1 || $memory>2048 || !in_array($checker,[-1,0,1,2,3,4],true)) {
    http_response_code(400); exit('题目名称、测试点数量、评测方式或资源限制无效。');
}
$existing = Problem::byFilename($name)[0]['pid'] ?? null;
if ($existing !== null && (int)$existing !== $id) { http_response_code(409); exit('题目文件名已使用。'); }
$readLevel = $editor ? (int)($_POST['readforce'] ?? 0) : 0;
$enabled = $editor && !empty($_POST['submitable']) ? 1 : 0;
$db = Database::connection();
$db->beginTransaction();
try {
    $fields = [$title, $name, $readLevel, $enabled, $cases, $time, $memory, $_POST['detail'] ?? ''];
    $options = [(int)($_POST['difficulty'] ?? 0), $checker, (int)($_POST['group'] ?? 1)];
    if ($action === 'add') {
        Problem::create(...array_merge($fields, [time(), $userId], $options));
        $id = (int)$db->lastInsertId();
    } else {
        Problem::edit(...array_merge($fields, $options, [$id]));
    }
    Category::setForProblem($id, array_keys(array_filter($_POST['cate'] ?? [])));
    if (!empty($_FILES['datafile']['size'])) {
        if (!is_uploaded_file($_FILES['datafile']['tmp_name'])) throw new DomainException('数据上传失败。');
        TestData::importZip($name, $cases, $_FILES['datafile']['tmp_name'], $editor);
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    if ($error instanceof DomainException) { http_response_code(400); exit(h($error->getMessage())); }
    throw $error;
}
i提示('题目已保存。', 取路径('problem/problem.php?pid='.$id));
