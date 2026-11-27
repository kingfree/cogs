<?php
/**
 * user/dologin.php 核验密码后建立会话。之后每次进入网页，bootstrap.php 会重新核对账号和权限。
 * 例：Login::start($user);  // $user 是刚经密码校验的账号记录。
 */
final class Login
{
    /** 核验密码并按需升级旧哈希。升级与其他请求的改密竞争时须重新登录。 */
    public static function authenticate(string $username, string $password): ?array
    {
        $user = Account::byUsername($username)[0] ?? null; // 登录名唯一，读取当前凭据后再核验输入。
        if (!$user || !Password::verify($password, $user['pwdhash'])) return null; // 错误提示统一，避免向匿名访问者区分账号是否存在。
        // 很长的历史密码仍按原算法验证，不能升级为会截断输入的 bcrypt。
        if (strlen($password) <= 72 && !str_contains($password, "\0") && password_needs_rehash($user['pwdhash'], PASSWORD_DEFAULT)) {
            $hash = Password::hash($password); // 只有验证通过的密码才能用于升级旧哈希。
            if (!Account::replaceVerifiedPassword($hash, $user['uid'], $user['pwdhash'])) return null;
            $user['pwdhash'] = $hash; // 用升级后的版本签发会话和长期令牌。
        }
        return $user;
    }

    /** 按登录时间倒序取记录；不传有效用户编号时返回全站记录，由入口控制可见范围。 */
    public static function history($userId)
    {
        $sql = 'SELECT l.*, u.email, u.realname FROM login l JOIN userinfo u ON u.uid=l.uid';
        if ($userId) return Database::rows($sql.' WHERE l.uid=? ORDER BY l.ltime DESC', [$userId]); // 个人日志只返回指定账号，管理员可另查全站记录。
        return Database::rows($sql.' ORDER BY l.ltime DESC');
    }

    /** 写入或清除长期 Cookie，HTTPS 请求中仅允许浏览器通过 HTTPS 再次发送。 */
    private static function cookie($token, $expires) {
        setcookie('cogs_login', $token, ['expires'=>$expires, 'path'=>'/', 'httponly'=>true,
            'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    }

    /** 用户选择记住登录时发放 90 天令牌；浏览器持原值，数据库只保存摘要。 */
    public static function saveToken($user) {
        $token = bin2hex(random_bytes(32)); // 浏览器持有 256 位随机原值，数据库仅保存摘要。
        $expires = time() + 7776000; // 旧站“保存 Cookies”约定为 90 天。
        Database::execute('DELETE FROM login_token WHERE expires_at <= ?', [time()]);
        // 长期令牌绑定发放时的密码哈希版本，改密后即使 Cookie 尚未过期也无法恢复登录。
        Database::execute('INSERT INTO login_token(digest, uid, expires_at, password_version) VALUES(?, ?, ?, ?)',
            [hash('sha256', $token), $user['uid'], $expires, hash('sha256', $user['pwdhash'])]);
        self::cookie($token, $expires); // 数据库已记下有效期，浏览器才收到可恢复登录的令牌。
    }

    /** 当浏览器没有活跃会话时尝试恢复。令牌不正确、过期或密码已改，都保持未登录。 */
    public static function restore() {
        if (empty($_COOKIE['cogs_login']) || !is_string($_COOKIE['cogs_login']) ||
            !preg_match('/^[0-9a-f]{64}$/D', $_COOKIE['cogs_login'])) return; // 格式不符合服务端签发的令牌时，保持匿名身份。
        $user = Database::rows('SELECT u.uid, u.pwdhash, t.password_version
            FROM login_token t JOIN userinfo u ON u.uid=t.uid WHERE t.digest=? AND t.expires_at>?',
            [hash('sha256', $_COOKIE['cogs_login']), time()])[0] ?? null;
        if ($user && hash_equals($user['password_version'], hash('sha256', $user['pwdhash']))) {
            self::start($user, true); // Cookie 恢复的登录记录标记为 (C)。
        }
    }

    /** 登录核验完成后更换会话 ID，重新读取账号资料，按站点配置记录登录事件。 */
    public static function start(array $user, bool $remembered = false): bool
    {
        global $SET, $cfg;
        // 先更换登录前的会话编号，再把账号资料载入新会话。
        session_regenerate_id(true);
        // 只接受刚刚通过核验的版本；在核验后并发改密时，loadUser 会清空此会话。
        $_SESSION = ['password_version'=>hash('sha256', $user['pwdhash'])];
        self::loadUser($user['uid']);
        if (empty($_SESSION['ID'])) return false; // 账号被删除或核验同时被改密时，不记成功登录。
        $ip = $_SERVER['REMOTE_ADDR']; // 最近访问地址使用连接来源，不信任表单中携带的地址。
        Database::execute('UPDATE userinfo SET lastip=? WHERE uid=?', [$ip, $user['uid']]);
        if ($SET['login_log']) { // 站点关闭登录历史时仍维护最近访问 IP。
            Database::execute('INSERT INTO login(uid, ua, ip, ltime, version) VALUES(?, ?, ?, NOW(), ?)',
                [$user['uid'], $_SERVER['HTTP_USER_AGENT'] ?? '', $ip, $cfg['dir_root'].($remembered ? '(C)' : '')]);
        }
        return true;
    }

    /** 注销当前浏览器的长期令牌，其他设备的令牌由各自的注销或密码版本控制。 */
    public static function forget() {
        if (isset($_COOKIE['cogs_login']) && is_string($_COOKIE['cogs_login'])) {
            Database::execute('DELETE FROM login_token WHERE digest=?', [hash('sha256', $_COOKIE['cogs_login'])]);
        }
        self::cookie('', time() - 3600); // 让浏览器删除当前设备上的长期凭据。
    }
    /** 从数据库刷新会话。密码版本变化会结束原会话，撤销的功能权限在下一次请求时消失。 */
    public static function loadUser($id): void
    {
        global $SET;
        $user = Account::byId($id)[0] ?? null; // 每次请求从用户记录更新会话，不从 Cookie 恢复账号属性。
        if (!$user) { $_SESSION = []; return; } // 被删除的账号不能沿用之前缓存的分组权限。
        $version = hash('sha256', $user['pwdhash']); // 比较不可逆版本摘要，避免把密码哈希写进会话。
        if (isset($_SESSION['password_version']) && !hash_equals($_SESSION['password_version'], $version)) {
            $_SESSION = []; // 当前密码已更改，同时撤回所有已加载的授权。
            session_regenerate_id(true);
            return;
        }
        $_SESSION['password_version'] = $version; // 下次刷新时再与数据库凭据对照。
        // 先抹掉上次请求缓存的全部授权，否则已撤销的权限仍会残留。
        foreach (array_keys($_SESSION) as $key) {
            if (preg_match('/^pri[0-9]+$/D', $key)) unset($_SESSION[$key]);
        }
        foreach (['ID'=>'uid', 'nickname'=>'nickname', 'readforce'=>'readforce', 'group'=>'gbelong',
                  'email'=>'email', 'grade'=>'grade', 'style'=>'style'] as $key=>$field) {
            $_SESSION[$key] = $user[$field]; // 会话使用历史页面约定的键名，如 ID 对应 uid。
        }
        $_SESSION['user_style'] = $user['user_style'] ?: $SET['user_style']; // 未单独选择主题的用户沿用站点设置。
        foreach (Privilege::forUser($id) as $grant) $_SESSION['pri'.$grant['pri']] = $grant['def'];
        $_SESSION['admin'] = Privilege::allows('管理用户'); // 管理菜单与各入口使用同一套继承规则。
    }
}
