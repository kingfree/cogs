<?php
// 站内文章和阅读范围。例如 Page::directory()。
final class Page
{
    // pg 是文章表的别名，公开目录和讨论使用同一阅读范围。
    public static function visibility(): array
    {
        $level = (int)$_SESSION['readforce'];
        if (Privilege::allows('查看页面')) return ['pg.`force` <= ?', [$level]];
        $groups = UserGroup::ancestors((int)$_SESSION['group']);
        $places = implode(',', array_fill(0, count($groups), '?'));
        return ["pg.`force` <= ? AND pg.`group` IN ($places)", array_merge([$level], $groups)];
    }

    public static function directory()
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT pg.aid, pg.title FROM page pg WHERE $scope ORDER BY pg.title", $values);
    }

    public static function recent()
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT pg.* FROM page pg WHERE $scope ORDER BY pg.etime DESC LIMIT 6", $values);
    }

    public static function remove($id)
    {
        return Database::execute(
            "DELETE FROM page WHERE aid = ?",
            [$id]
        );
    }

    public static function edit($title, $readLevel, $text, $groupId, $edited, $id)
    {
        return Database::execute(
            "UPDATE page SET title=?, `force`=?, `text`=?, `group`=?, etime=? WHERE aid=?",
            [$title, $readLevel, $text, $groupId, $edited, $id]
        );
    }

    public static function create($title, $readLevel, $text, $groupId, $edited, $created, $author)
    {
        return Database::execute(
            "INSERT INTO page(title, `force`, `text`, `group`, etime, `time`, uid) VALUES(?, ?, ?, ?, ?, ?, ?)",
            [$title, $readLevel, $text, $groupId, $edited, $created, $author]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM page WHERE aid=?",
            [$id]
        );
    }

    public static function forReader($id)
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT pg.*, pg.`group` AS gid, g.gname, u.nickname
            FROM page pg LEFT JOIN groups g ON g.gid=pg.`group` JOIN userinfo u ON u.uid=pg.uid
            WHERE pg.aid=? AND $scope", array_merge([$id], $values))[0] ?? null;
    }

    public static function search($key)
    {
        [$scope, $values] = self::visibility();
        $sql = "SELECT pg.*, pg.`group` AS gid, g.gname, u.nickname, u.email, u.realname FROM page pg
            LEFT JOIN groups g ON g.gid=pg.`group` JOIN userinfo u ON u.uid=pg.uid WHERE $scope";
        if ($key !== '') {
            $sql .= " AND (pg.text LIKE ? OR pg.title LIKE ?)";
            array_push($values, '%'.$key.'%', '%'.$key.'%');
        }
        return Database::rows($sql." ORDER BY pg.aid DESC", $values);
    }
}
