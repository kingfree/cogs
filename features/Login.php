<?php
/**
 * user/dologin.php 核验密码后建立会话。之后每次进入网页，bootstrap.php 会重新核对账号和权限。
 * 例：Login::start($user);  // $user 是刚经密码校验的账号记录。
 */
final class Login
{
    /** 清理已过期的长期登录令牌，续发令牌前调用。 */
    public static function expireTokens($now)
    {
        return Database::execute(
            "DELETE FROM login_token WHERE expires_at <= ?",
            [$now]
        );
    }

    /** 保存令牌摘要、有效期及发放时的密码版本。 */
    public static function remember($digest, $userId, $expires, $version)
    {
        return Database::execute(
            "INSERT INTO login_token(digest, uid, expires_at, password_version) VALUES(?, ?, ?, ?)",
            [$digest, $userId, $expires, $version]
        );
    }

    /** 按令牌摘要查找仍在有效期内的账号，恢复时还要核对密码版本。 */
    public static function byToken($digest, $now)
    {
        return Database::rows(
            "SELECT u.*, t.password_version
             FROM login_token t JOIN userinfo u ON u.uid = t.uid
             WHERE t.digest = ? AND t.expires_at > ?",
            [$digest, $now]
        );
    }

    /** 成功登录后更新账号的最近访问 IP。 */
    public static function updateAddress($ip, $userId)
    {
        return Database::execute(
            "UPDATE userinfo SET lastip = ? WHERE uid = ?",
            [$ip, $userId]
        );
    }

    /** 记录一次登录事件，version 同时区分通过 Cookie 还原的会话。 */
    public static function record($userId, $agent, $ip, $version)
    {
        return Database::execute(
            "INSERT INTO login(uid, ua, ip, ltime, version) VALUES(?, ?, ?, NOW(), ?)",
            [$userId, $agent, $ip, $version]
        );
    }

    /** 撤销单个已发放令牌的摘要。 */
    public static function revoke($digest)
    {
        return Database::execute(
            "DELETE FROM login_token WHERE digest = ?",
            [$digest]
        );
    }

    /** 按登录时间倒序取记录；不传有效用户编号时返回全站记录，由入口控制可见范围。 */
    public static function history($userId)
    {
        $sql = 'SELECT l.*, u.email, u.realname FROM login l JOIN userinfo u ON u.uid=l.uid';
        if ($userId) return Database::rows($sql.' WHERE l.uid=? ORDER BY l.ltime DESC', [$userId]);
        return Database::rows($sql.' ORDER BY l.ltime DESC');
    }

    /** 写入或清除长期 Cookie，HTTPS 请求中仅允许浏览器通过 HTTPS 再次发送。 */
    public static function cookie($token, $expires) {
        setcookie('cogs_login', $token, ['expires'=>$expires, 'path'=>'/', 'httponly'=>true,
            'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    }

    /** 用户选择记住登录时发放 90 天令牌；浏览器持原值，数据库只保存摘要。 */
    public static function saveToken($user) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + 7776000;
        Login::expireTokens(time());
        // 长期令牌绑定发放时的密码哈希版本，改密后即使 Cookie 尚未过期也无法恢复登录。
        Login::remember(hash('sha256', $token), $user['uid'], $expires, hash('sha256', $user['pwdhash']));
        self::cookie($token, $expires);
    }

    /** 当浏览器没有活跃会话时尝试恢复。令牌不正确、过期或密码已改，都保持未登录。 */
    public static function restore() {
        if (empty($_COOKIE['cogs_login']) || !is_string($_COOKIE['cogs_login']) ||
            !preg_match('/^[0-9a-f]{64}$/D', $_COOKIE['cogs_login'])) return;
        $user = Login::byToken(hash('sha256', $_COOKIE['cogs_login']), time())[0] ?? false;
        if ($user && hash_equals($user['password_version'], hash('sha256', $user['pwdhash']))) {
            self::start($user, true);
        }
    }

    /** 登录核验完成后更换会话 ID，重新读取账号资料，按站点配置记录登录事件。 */
    public static function start($user, $remembered = false) {
        global $SET, $cfg;
        // 先更换登录前的会话编号，再把账号资料载入新会话。
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

    /** 注销当前浏览器的长期令牌，其他设备的令牌由各自的注销或密码版本控制。 */
    public static function forget() {
        if (isset($_COOKIE['cogs_login']) && is_string($_COOKIE['cogs_login'])) {
            Login::revoke(hash('sha256', $_COOKIE['cogs_login']));
        }
        self::cookie('', time() - 3600);
    }
    /** 从数据库刷新会话。密码版本变化会结束原会话，撤销的功能权限在下一次请求时消失。 */
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
        // 先抹掉上次请求缓存的全部授权，否则已撤销的权限仍会残留。
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
