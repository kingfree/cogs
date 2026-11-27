<?php
/**
 * 注册、登录、重置密码与找回答案共用这一套规则。历史哈希保留校验能力，新写入的凭据统一用 PHP 的密码算法。
 * 例：$matches = Password::verify($enteredPassword, $user['pwdhash']);
 */
final class Password
{
    /** 核对用户输入和存储哈希。兼容旧版 32 位十六进制凭据，是否升级由登录入口决定。 */
    public static function verify($text, $hash): bool
    {
        if (!is_string($text) || $text === '' || !is_string($hash)) return false;
        // 32 位旧凭据只能用历史算法核对；成功后由登录入口升级并回写。
        if (preg_match('/^[a-f0-9]{32}$/D', $hash)) {
            return hash_equals($hash, md5(sha1($text).'hasyzxcmykrgb123'));
        }
        return password_verify($text, $hash);
    }

    /** 接受 1 至 72 字节且不含 NUL 的密码或答案，避免 bcrypt 截断不同的长输入。 */
    public static function hash($text): string
    {
        if (!is_string($text) || $text === '' || strlen($text) > 72 || str_contains($text, "\0")) {
            throw new InvalidArgumentException('密码或找回答案须为 1 至 72 字节。');
        }
        return password_hash($text, PASSWORD_DEFAULT);
    }
}
