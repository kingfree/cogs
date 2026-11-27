<?php
// 接收 user/panel.php 的 POST。edit 保存个人资料，editpwd 改密码，editpwdans 改找回答案；三者均针对当前登录账号。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
$uid = (int)$_SESSION['ID'];
$return = 取路径('user/panel.php');
$action = $_POST['action'] ?? '';
if ($action === 'edit') {
    $name = trim($_POST['usr'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $realName = trim($_POST['realname'] ?? '');
    $theme = $_POST['user_style'] ?? '';
    if (!preg_match('/^[A-Za-z0-9_]{1,24}$/D', $name) || $nickname === '' || mb_strlen($nickname) > 16 ||
        mb_strlen($realName) > 16 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z0-9_-]{0,20}$/D', $theme)) {
        i异常('用户名、昵称、邮箱或主题格式无效。', $return);
    }
    if (Account::usernameInUse($name, $uid)) i异常('用户名已存在。', $return);
    if (!empty($_FILES['file']['tmp_name'])) {
        $file = $_FILES['file'];
        $info = is_uploaded_file($file['tmp_name']) ? @getimagesize($file['tmp_name']) : null;
        if (!$info || $file['size'] > 4194304 || $info[0]*$info[1]>12000000 ||
            !in_array($info[2], [IMAGETYPE_PNG,IMAGETYPE_JPEG,IMAGETYPE_GIF], true)) i异常('背景图片格式或大小无效。', $return);
        $image = imagecreatefromstring(file_get_contents($file['tmp_name']));
        $dir = dirname(__DIR__).'/images/background';
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        // 背景图先解码再写入临时 PNG，新图完整写入后替换旧图。
        $temp = tempnam($dir, '.background-');
        try {
            if (!$image || !imagepng($image, $temp) || !rename($temp, "$dir/$uid.png")) i异常('背景图片保存失败。', $return);
        } finally { if (is_file($temp)) unlink($temp); }
    }
    Account::editProfile($name, $nickname, $realName, $email, trim($_POST['memo'] ?? ''), $theme, (int)!empty($_POST['style']), $uid);
    Login::loadUser($uid);
    i提示('个人资料已保存。', $return);
    exit;
}
// 变更密码或找回问题前再次核对旧密码。
$user = Account::credentials($uid)[0] ?? null;
if (!$user || !Password::verify($_POST['opwd'] ?? '', $user['pwdhash'])) i异常('旧密码不正确。', $return);
try {
    if ($action === 'editpwd') {
        $new = $_POST['npwd1'] ?? '';
        if ($new !== ($_POST['npwd2'] ?? null)) i异常('两次输入的密码不一致。', $return);
        $hash = Password::hash($new);
        Account::changePassword($hash, $uid);
        // 已核验旧密码的当前会话改用新版本；其他会话下次载入仍会看到版本变化。
        $_SESSION['password_version'] = hash('sha256', $hash);
        Login::forget();
        session_regenerate_id(true);
        i提示('密码已修改。', $return);
    } elseif ($action === 'editpwdans') {
        $question = trim($_POST['qus'] ?? '');
        $answer = $_POST['ans'] ?? '';
        if (mb_strlen($question) > 64) i异常('提示问题过长。', $return);
        Account::recovery($question, $answer === '' ? '' : Password::hash($answer), $uid);
        i提示('密码提示问题已修改。', $return);
    } else { http_response_code(400); exit('未知操作。'); }
} catch (InvalidArgumentException $error) { i异常($error->getMessage(), $return); }
