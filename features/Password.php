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
        if (!is_string($text) || $text === '' || !is_string($hash)) return false; // 空密码和未设置的找回答案都不能通过验证。
        // 32 位旧凭据只能用历史算法核对；成功后由登录入口升级并回写。
        if (preg_match('/^[a-f0-9]{32}$/D', $hash)) {
            return hash_equals($hash, md5(sha1($text).'hasyzxcmykrgb123')); // 与历史版本生成的摘要做恒时比较。
        }
        return password_verify($text, $hash); // 新格式根据哈希本身记录的算法校验。
    }

    /** 接受 1 至 72 字节且不含 NUL 的密码或答案，避免 bcrypt 截断不同的长输入。 */
    public static function hash($text): string
    {
        if (!is_string($text) || $text === '' || strlen($text) > 72 || str_contains($text, "\0")) {
            throw new InvalidArgumentException('密码或找回答案须为 1 至 72 字节。');
        }
        return password_hash($text, PASSWORD_DEFAULT); // 数据库只保存 PHP 产生的含盐哈希。
    }
    /** 找回问题最多 64 字；允许留空答案来关闭答案找回。 */
    public static function recovery(string $question, string $answer): array
    {
        $question = trim($question); // 问题作为展示文字清理空白，答案保持用户原始输入。
        if (mb_strlen($question) > 64) throw new InvalidArgumentException('提示问题过长。');
        return ['question'=>$question, 'answerHash'=>$answer === '' ? '' : self::hash($answer)]; // 空答案会关闭基于答案的找回方式。
    }

    /** 管理员重置和答案找回共用临时密码格式，明文只在成功响应中显示一次。 */
    public static function temporary(): string
    {
        return bin2hex(random_bytes(12));
    }
}
