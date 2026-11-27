<?php
/**
 * 站内信的正文只对收发双方可见。mail/inbox.php 把当前用户 ID 同时用于匹配发件人和收件人。
 * 例：$letters = Message::received((int)$_SESSION['ID']);
 */
final class Message
{
    /** 发件箱按时间倒序显示，用收件人账号补充列表资料。 */
    public static function sent($userId)
    {
        return Database::rows(
            "SELECT mail.*, userinfo.*
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
            "SELECT mail.*, userinfo.*
             FROM mail, userinfo
             WHERE mail.toid = ? AND mail.fromid = userinfo.uid
             ORDER BY mid DESC",
            [$userId]
        );
    }

    /** 用同一个当前用户 ID 匹配发件人和收件人。查无记录或无权时都返回空列表。 */
    public static function visible($id, $sender, $recipient)
    {
        return Database::rows(
            "SELECT * FROM mail WHERE mid = ? AND (fromid = ? OR toid = ?)",
            [$id, $sender, $recipient]
        );
    }

    /** 只允许收件人使信件变成已读，发件人打开自己的邮件不会替对方读信。 */
    public static function markRead($id, $recipient)
    {
        return Database::execute(
            "UPDATE mail SET readed = 1 WHERE mid = ? AND toid = ?",
            [$id, $recipient]
        );
    }

    /** 发送时间和发件人由调用入口提供，新信默认未读。 */
    public static function send($sender, $recipient, $time, $title, $body)
    {
        return Database::execute(
            "INSERT INTO mail(fromid, toid, time, readed, title, msg) VALUES(?, ?, ?, 0, ?, ?)",
            [$sender, $recipient, $time, $title, $body]
        );
    }

    /** 返回 received（我尚未读）与 sent（对方尚未读）两个数量。 */
    public static function unreadCounts($userId): array
    {
        return Database::rows("SELECT COUNT(CASE WHEN toid=? THEN 1 END) AS received,
            COUNT(CASE WHEN fromid=? THEN 1 END) AS sent FROM mail WHERE readed=0 AND (toid=? OR fromid=?)",
            [$userId, $userId, $userId, $userId])[0];
    }

}
