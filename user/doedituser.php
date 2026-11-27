<?php
// 管理员从 user/edituser.php 提交账号变更；例如 POST 到 doedituser.php?action=edit&uid=2 可保存目标用户资料。
require_once(__DIR__.'/../include/header.php');
if (($_POST['reset'] ?? '') === 'reset') header('Cache-Control: no-store'); // 响应可能显示一次性临时密码。
gethead(8, '修改用户', '');
requirePost();
$target = (int)($_GET['uid'] ?? 0); // 表单的操作对象由 URL 中的账号编号确定。
$action = $_GET['action'] ?? ''; // 删除和编辑都受下方管理账号保护约束。
// 除了入口权限，还要看目标账号持有哪些授权，防止普通维护操作改到特权账号。
$grants = array_column(Privilege::forUser($target), 'pri'); // 还需辨别目标账号的现有授权，防止利用资料修改接管管理账号。
if ($target === 1 || in_array(2, $grants)) {
    if (!Privilege::allows('超级用户') || $action === 'del') { http_response_code(403); exit('不能更改该管理账号。'); } // 特权账号不能经通用用户删除入口移除。
} elseif ($grants && !Privilege::allows('修改权限')) { http_response_code(403); exit('需要修改权限。'); } // 有明确授权的账号必须由能修改权限的管理员维护。


if ($action === 'del') {
    if (!Account::remove($target)) 异常('无此用户！', 取路径('user/index.php')); // 受影响行数同时区分用户不存在和确实删除。
    提示("删除用户成功！",取路径("user/index.php"));
} else if ($action === 'edit') {
    $resetNotice="";
    Account::administer($_POST['nickname'], $_POST['readforce'], $_POST['email'], $_POST['memo'], $_POST['realname'], $_POST['gbelong'], $target); // 个人基本资料与所属组由负责维护的管理员提交。
    if (($_POST['reset'] ?? '') === 'reset') {
        // 重置后只在这次响应显示临时密码，数据库保存其哈希。
        $temporary = Password::temporary(); // 管理员只在这一次响应看到临时密码。
        Account::changePassword(Password::hash($temporary), $target); // 客户端旧会话在下次请求时因密码版本变化失效。
        $resetNotice=" 临时密码为 ".h($temporary);
    }
    提示("编辑用户成功！$resetNotice",取路径("user/detail.php?uid=$target"));
}
?>
