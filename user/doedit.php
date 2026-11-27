<?php
// 接收 user/panel.php 的 POST。edit 保存个人资料，editpwd 改密码，editpwdans 改找回答案；三者均针对当前登录账号。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
$uid = (int)$_SESSION['ID']; // 账号只取自会话，表单和查询串中的 uid 均不参与更新。
$return = 取路径('user/panel.php');
$action = $_POST['action'] ?? ''; // 三种表单共享入口；只有修改凭据时需要再核验旧密码。
if ($action === 'edit') {
    $theme = $_POST['user_style'] ?? ''; // 主题来自可选样式名，下一步限制它能使用的字符。
    if (!preg_match('/^[A-Za-z0-9_-]{0,20}$/D', $theme)) i异常('主题格式无效。', $return);
    try {
        $profile = Account::profileFields($_POST); // 与注册使用相同规范，修改自己的登录名也处理重名。
        Account::editProfile($uid, $profile, $theme, !empty($_POST['style']), $_FILES['file'] ?? null); // 有新图时连同资料发布，未上传时保留当前背景。
    } catch (InvalidArgumentException $error) {
        i异常($error->getMessage(), $return);
    }
    Login::loadUser($uid); // 立即刷新页头展示的昵称、主题和邮箱。
    i提示('个人资料已保存。', $return);
    exit;
}
// 变更密码或找回问题前再次核对旧密码。
$user = Account::credentials($uid)[0] ?? null; // 取数据库的凭据，避免依据会话旧副本修改密码。
if (!$user || !Password::verify($_POST['opwd'] ?? '', $user['pwdhash'])) i异常('旧密码不正确。', $return);
try {
    if ($action === 'editpwd') {
        $new = $_POST['npwd1'] ?? ''; // 保留密码开头和结尾的字符。
        if ($new !== ($_POST['npwd2'] ?? null)) i异常('两次输入的密码不一致。', $return);
        $hash = Password::hash($new); // 哈希成功后才替换刚刚验证通过的凭据。
        if (!Account::replaceVerifiedPassword($hash, $uid, $user['pwdhash'])) i异常('账号凭据已变更，请重新登录。', $return);
        // 已核验旧密码的当前会话改用新版本；其他会话下次载入仍会看到版本变化。
        $_SESSION['password_version'] = hash('sha256', $hash);
        Login::forget(); // 主动撤销本设备改密前签发的长期令牌。
        session_regenerate_id(true); // 改密后为当前会话更换标识。
        i提示('密码已修改。', $return);
    } elseif ($action === 'editpwdans') {
        $recovery = Password::recovery($_POST['qus'] ?? '', $_POST['ans'] ?? ''); // 提示问题可展示，答案不以明文回写。
        $updated = Account::changeVerifiedRecovery($uid, $user['pwdhash'], $recovery); // 以刚核验的密码版本作为写入条件。
        if (!$updated) i异常('账号凭据已变更，请重新登录。', $return); // 核验后又被改密的旧请求需要重新认证。
        i提示('密码提示问题已修改。', $return);
    } else { http_response_code(400); exit('未知操作。'); } // 非表单定义的操作不能默认当成普通资料修改。
} catch (InvalidArgumentException $error) { i异常($error->getMessage(), $return); }
