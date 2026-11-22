<?php
// 登录记录和记住登录的令牌。例如 Login::expireTokens($now)。
final class Login
{
    public static function expireTokens($now)
    {
        return Database::execute(
            "DELETE FROM login_token WHERE expires_at <= ?",
            [$now]
        );
    }

    public static function remember($digest, $userId, $expires, $version)
    {
        return Database::execute(
            "INSERT INTO login_token(digest, uid, expires_at, password_version) VALUES(?, ?, ?, ?)",
            [$digest, $userId, $expires, $version]
        );
    }

    public static function byToken($digest, $now)
    {
        return Database::rows(
            "SELECT u.*, t.password_version
             FROM login_token t JOIN userinfo u ON u.uid = t.uid
             WHERE t.digest = ? AND t.expires_at > ?",
            [$digest, $now]
        );
    }

    public static function updateAddress($ip, $userId)
    {
        return Database::execute(
            "UPDATE userinfo SET lastip = ? WHERE uid = ?",
            [$ip, $userId]
        );
    }

    public static function record($userId, $agent, $ip, $version)
    {
        return Database::execute(
            "INSERT INTO login(uid, ua, ip, ltime, version) VALUES(?, ?, ?, NOW(), ?)",
            [$userId, $agent, $ip, $version]
        );
    }

    public static function revoke($digest)
    {
        return Database::execute(
            "DELETE FROM login_token WHERE digest = ?",
            [$digest]
        );
    }

    public static function history($userId)
    {
        $sql = 'SELECT l.*, u.email, u.realname FROM login l JOIN userinfo u ON u.uid=l.uid';
        if ($userId) return Database::rows($sql.' WHERE l.uid=? ORDER BY l.ltime DESC', [$userId]);
        return Database::rows($sql.' ORDER BY l.ltime DESC');
    }

    public static function cookie($token, $expires) {
        setcookie('cogs_login', $token, ['expires'=>$expires, 'path'=>'/', 'httponly'=>true,
            'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    }

    public static function saveToken($user) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + 7776000;
        Login::expireTokens(time());
        Login::remember(hash('sha256', $token), $user['uid'], $expires, hash('sha256', $user['pwdhash']));
        self::cookie($token, $expires);
    }

    public static function restore() {
        if (empty($_COOKIE['cogs_login']) || !is_string($_COOKIE['cogs_login']) ||
            !preg_match('/^[0-9a-f]{64}$/D', $_COOKIE['cogs_login'])) return;
        $user = Login::byToken(hash('sha256', $_COOKIE['cogs_login']), time())[0] ?? false;
        if ($user && hash_equals($user['password_version'], hash('sha256', $user['pwdhash']))) {
            self::start($user, true);
        }
    }

    public static function start($user, $remembered = false) {
        global $SET, $cfg;
        session_regenerate_id(true);
        $_SESSION = array();
        self::loadUser($user['uid']);
        $ip = $_SERVER['REMOTE_ADDR'];
        Login::updateAddress($ip, $user['uid']);
        if ($SET['login_log']) {
            Login::record($user['uid'], isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', $ip,
                    $cfg['dir_root'].($remembered ? '(C)' : ''));
        }
    }

    public static function forget() {
        if (isset($_COOKIE['cogs_login']) && is_string($_COOKIE['cogs_login'])) {
            Login::revoke(hash('sha256', $_COOKIE['cogs_login']));
        }
        self::cookie('', time() - 3600);
    }
    public static function loadUser($id): void
    {
        global $SET;
        $user = Account::byId($id)[0] ?? null;
        if (!$user) { $_SESSION = []; return; }
        $version = hash('sha256', $user['pwdhash']);
        if (isset($_SESSION['password_version']) && !hash_equals($_SESSION['password_version'], $version)) {
            $_SESSION = [];
            session_regenerate_id(true);
            return;
        }
        $_SESSION['password_version'] = $version;
        foreach (array_keys($_SESSION) as $key) {
            if (preg_match('/^pri[0-9]+$/D', $key)) unset($_SESSION[$key]);
        }
        foreach (['ID'=>'uid', 'nickname'=>'nickname', 'readforce'=>'readforce', 'group'=>'gbelong',
                  'email'=>'email', 'grade'=>'grade', 'style'=>'style'] as $key=>$field) {
            $_SESSION[$key] = $user[$field];
        }
        $_SESSION['user_style'] = $user['user_style'] ?: $SET['user_style'];
        foreach (Privilege::forUser($id) as $grant) $_SESSION['pri'.$grant['pri']] = $grant['def'];
        $_SESSION['admin'] = Privilege::allows('管理用户');
    }
}
