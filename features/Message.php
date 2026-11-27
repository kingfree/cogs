<?php
/**
 * 站内信的正文只对收发双方可见。mail/inbox.php 打开正文时用会话身份匹配收发双方。
 * 例：$letters = Message::received((int)$_SESSION['ID']);
 */
final class Message
{
    /** 发件箱按时间倒序显示，用收件人账号补充列表资料。 */
    public static function sent($userId)
    {
        return Database::rows(
            "SELECT mail.*, userinfo.uid AS contact_id, userinfo.email, userinfo.nickname
             FROM mail, userinfo
             WHERE mail.fromid = ? AND mail.toid = userinfo.uid
             ORDER BY mid DESC",
            [$userId]
        );
    }

    /** 收件箱只列出发给该账号的信，用发件人账号补充列表资料。 */
    public static function received($userId)
    {
        return Database::rows(
            "SELECT mail.*, userinfo.uid AS contact_id, userinfo.email, userinfo.nickname
             FROM mail, userinfo
             WHERE mail.toid = ? AND mail.fromid = userinfo.uid
             ORDER BY mid DESC",
            [$userId]
        );
    }

    /** 打开一封信。无权或不存在都返回 null，收件人首次打开时更新未读数。 */
    public static function open(int $id, int $viewerId): ?array
    {
        $message = Database::rows('SELECT * FROM mail WHERE mid=? AND (fromid=? OR toid=?)',
            [$id, $viewerId, $viewerId])[0] ?? null; // 收发任一方都可阅读。其他人的信不会进入 PHP。
        if ($message && !(int)$message['readed'] && (int)$message['toid'] === $viewerId) {
            self::markRead($id, $viewerId); // 发件人查看历史记录不改变对方收件箱。
        }
        return $message; // 查询落空时返回 null，由入口统一显示“无权或不存在”。
    }

    /** 只允许收件人使信件变成已读，发件人打开自己的邮件不会替对方读信。 */
    public static function markRead($id, $recipient)
    {
        return Database::execute(
            "UPDATE mail SET readed = 1 WHERE mid = ? AND toid = ?",
            [$id, $recipient] // 收件人校验在更新条件里，旧弹窗也无法修改别人的未读数。
        );
    }

    /** 按会话中的发件人投递新信；收件人账号不存在时返回 false，发件箱不会出现无法投递的信。 */
    public static function send(int $sender, int $recipient, string $title, string $body): bool
    {
        return (bool)Database::execute(
            "INSERT INTO mail(fromid, toid, time, readed, title, msg)
             SELECT ?, uid, ?, 0, ?, ? FROM userinfo WHERE uid=?",
            [$sender, time(), $title, $body, $recipient] // 用收件人真实账号生成待投递记录。
        );
    }

    /** 返回 received（我尚未读）与 sent（对方尚未读）两个数量。 */
    public static function unreadCounts($userId): array
    {
        return Database::rows("SELECT COUNT(CASE WHEN toid=? THEN 1 END) AS received,
            COUNT(CASE WHEN fromid=? THEN 1 END) AS sent FROM mail WHERE readed=0 AND (toid=? OR fromid=?)",
            [$userId, $userId, $userId, $userId])[0]; // 计数查询即使没有信件也会返回一行零值。
    }

}
