<?php
/**
 * 文章列表、全文和讨论列表共用阅读条件。page/page.php 根据当前会话决定是否显示一篇文章。
 * 例：$page = Page::forReader(1);  // 页面不存在或不可见时返回 null。
 */
final class Page
{
    /** 返回 SQL 阅读条件和按顺序绑定的参数；调用者须使用 pg 作为 page 的表别名。 */
    public static function visibility(): array
    {
        $level = (int)$_SESSION['readforce'];
        // 单独授予查看页面权限者可跨组阅读，最低阅读等级仍生效。
        if (Privilege::allows('查看页面')) return ['pg.`force` <= ?', [$level]];
        $groups = UserGroup::ancestors((int)$_SESSION['group']);
        $places = implode(',', array_fill(0, count($groups), '?'));
        return ["pg.`force` <= ? AND pg.`group` IN ($places)", array_merge([$level], $groups)];
    }

    /** 站点地图只列当前读者可以打开的文章。 */
    public static function directory()
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT pg.aid, pg.title FROM page pg WHERE $scope ORDER BY pg.title", $values);
    }

    /** 首页显示最近编辑且当前可读的六篇文章。 */
    public static function recent()
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT pg.* FROM page pg WHERE $scope ORDER BY pg.etime DESC LIMIT 6", $values);
    }

    /** 按编号删除文章，调用它的管理页先做写权限检查。 */
    public static function remove($id)
    {
        return Database::execute(
            "DELETE FROM page WHERE aid = ?",
            [$id]
        );
    }

    /** 编辑后保留创建人和创建时间，记录本次修改时间。 */
    public static function edit($title, $readLevel, $text, $groupId, $edited, $id)
    {
        return Database::execute(
            "UPDATE page SET title=?, `force`=?, `text`=?, `group`=?, etime=? WHERE aid=?",
            [$title, $readLevel, $text, $groupId, $edited, $id]
        );
    }

    /** 新文章保存作者、开放分组及最低阅读等级。 */
    public static function create($title, $readLevel, $text, $groupId, $edited, $created, $author)
    {
        return Database::execute(
            "INSERT INTO page(title, `force`, `text`, `group`, etime, `time`, uid) VALUES(?, ?, ?, ?, ?, ?, ?)",
            [$title, $readLevel, $text, $groupId, $edited, $created, $author]
        );
    }

    /** 管理页按编号取原始文章，公开阅读入口应调用 forReader。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM page WHERE aid=?",
            [$id]
        );
    }

    /** 返回可展示的文章、分组名和作者；缺失及不可读均返回 null。 */
    public static function forReader($id)
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT pg.*, pg.`group` AS gid, g.gname, u.nickname
            FROM page pg LEFT JOIN groups g ON g.gid=pg.`group` JOIN userinfo u ON u.uid=pg.uid
            WHERE pg.aid=? AND $scope", array_merge([$id], $values))[0] ?? null;
    }

    /** 在当前会话的可读文章中搜索标题或正文；空关键词列出全部可读文章。 */
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
