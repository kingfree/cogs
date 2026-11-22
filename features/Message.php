<?php
// 站内信的收发和已读状态。例如 Message::sent($userId)。
final class Message
{
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

    public static function visible($id, $sender, $recipient)
    {
        return Database::rows(
            "SELECT * FROM mail WHERE mid = ? AND (fromid = ? OR toid = ?)",
            [$id, $sender, $recipient]
        );
    }

    public static function markRead($id, $recipient)
    {
        return Database::execute(
            "UPDATE mail SET readed = 1 WHERE mid = ? AND toid = ?",
            [$id, $recipient]
        );
    }

    public static function send($sender, $recipient, $time, $title, $body)
    {
        return Database::execute(
            "INSERT INTO mail(fromid, toid, time, readed, title, msg) VALUES(?, ?, ?, 0, ?, ?)",
            [$sender, $recipient, $time, $title, $body]
        );
    }

    public static function unreadCounts($userId): array
    {
        return Database::rows("SELECT COUNT(CASE WHEN toid=? THEN 1 END) AS received,
            COUNT(CASE WHEN fromid=? THEN 1 END) AS sent FROM mail WHERE readed=0 AND (toid=? OR fromid=?)",
            [$userId, $userId, $userId, $userId])[0];
    }

}
