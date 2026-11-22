<?php
// Cookie 中只保存随机令牌。数据库保存摘要，改密后旧令牌自动失效。
function loginCookie($token, $expires) {
    setcookie('cogs_login', $token, $expires, '/', '', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', true);
}

function rememberLogin($user) {
    $token = bin2hex(random_bytes(32));
    $expires = time() + 7776000;
    $db = new DataAccess();
    $db->dosql("delete from login_token where expires_at <= ?", array(time()));
    $db->dosql("insert into login_token(digest, uid, expires_at, password_version) values(?, ?, ?, ?)",
        array(hash('sha256', $token), $user['uid'], $expires, hash('sha256', $user['pwdhash'])));
    loginCookie($token, $expires);
}

function restoreLogin() {
    if (empty($_COOKIE['cogs_login']) || !is_string($_COOKIE['cogs_login']) ||
        !preg_match('/^[0-9a-f]{64}$/D', $_COOKIE['cogs_login'])) return;
    $db = new DataAccess();
    $db->dosql("select u.*, t.password_version from login_token t join userinfo u on u.uid = t.uid where t.digest = ? and t.expires_at > ?",
        array(hash('sha256', $_COOKIE['cogs_login']), time()));
    $user = $db->rtnrlt();
    if ($user && hash_equals($user['password_version'], hash('sha256', $user['pwdhash']))) {
        startLogin($user, true);
    }
}

function startLogin($user, $remembered = false) {
    global $LIB, $SET, $cfg;
    session_regenerate_id(true);
    $_SESSION = array();
    $LIB->get_userinfo((int)$user['uid']);
    $db = new DataAccess();
    $ip = $_SERVER['REMOTE_ADDR'];
    $db->dosql("update userinfo set lastip = ? where uid = ?", array($ip, $user['uid']));
    if ($SET['login_log']) {
        $db->dosql("insert into login(uid, ua, ip, ltime, version) values(?, ?, ?, NOW(), ?)",
            array($user['uid'], isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', $ip,
                $cfg['dir_root'].($remembered ? '(C)' : '')));
    }
}

function forgetLogin() {
    if (isset($_COOKIE['cogs_login']) && is_string($_COOKIE['cogs_login'])) {
        $db = new DataAccess();
        $db->dosql("delete from login_token where digest = ?", array(hash('sha256', $_COOKIE['cogs_login'])));
    }
    loginCookie('', time() - 3600);
}
