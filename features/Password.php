<?php
// 旧账号在成功登录时升级。例 Password::verify($text, $storedHash)。
final class Password
{
    public static function verify($text, $hash): bool
    {
        if (!is_string($text) || $text === '' || !is_string($hash)) return false;
        if (preg_match('/^[a-f0-9]{32}$/D', $hash)) {
            return hash_equals($hash, md5(sha1($text).'hasyzxcmykrgb123'));
        }
        return password_verify($text, $hash);
    }

    public static function hash($text): string
    {
        if (!is_string($text) || $text === '' || strlen($text) > 72 || str_contains($text, "\0")) {
            throw new InvalidArgumentException('密码或找回答案须为 1 至 72 字节。');
        }
        return password_hash($text, PASSWORD_DEFAULT);
    }
}
