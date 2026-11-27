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
        $level = (int)$_SESSION['readforce']; // 读者等级由已刷新会话提供。
        // 单独授予查看页面权限者可跨组阅读，最低阅读等级仍生效。
        if (Privilege::allows('查看页面')) return ['pg.`force` <= ?', [$level]];
        $groups = UserGroup::ancestors((int)$_SESSION['group']); // 所在组及祖先组都可以接收面向该组的文章。
        $places = implode(',', array_fill(0, count($groups), '?')); // 每个可见分组分别绑定，组编号不进入 SQL 字符串。
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

    /** 编辑与新建共用文章字段。库字段 text 是字节限长；标题去掉首尾空白，富文本原样留给展示层净化。 */
    public static function fields(array $form): array
    {
        $title = trim($form['title'] ?? ''); // 纯空白不能在文章目录中当作标题。
        $text = $form['text'] ?? null; // 富文本保留原始换行，以便再次编辑。
        $readLevel = filter_var($form['force'] ?? null, FILTER_VALIDATE_INT);
        $groupId = filter_var($form['group'] ?? null, FILTER_VALIDATE_INT); // 缺失或非法组号不能被转换成代表公开的 0。
        if ($title === '' || mb_strlen($title) > 200 || $text === null || strlen($text) > 65535 ||
            $readLevel === false || $groupId === false) throw new InvalidArgumentException('页面内容无效');
        return ['title'=>$title, 'text'=>$text, 'readLevel'=>$readLevel, 'groupId'=>$groupId];
    }

    /** 编辑保留创建人、创建时间，只改本次提交的字段。 */
    public static function edit(int $id, array $page): void
    {
        Database::execute('UPDATE page SET title=?, `force`=?, `text`=?, `group`=?, etime=? WHERE aid=?',
            [$page['title'], $page['readLevel'], $page['text'], $page['groupId'], time(), $id]);
    }

    /** 创建时作者来自登录会话，创建时间与编辑时间相同。返回新文章编号用于跳转。 */
    public static function create(int $author, array $page): int
    {
        $now = time(); // 新建文章只有一个时刻，同时作为创建和最近编辑时间。
        Database::execute('INSERT INTO page(title, `force`, `text`, `group`, etime, `time`, uid) VALUES(?, ?, ?, ?, ?, ?, ?)',
            [$page['title'], $page['readLevel'], $page['text'], $page['groupId'], $now, $now, $author]);
        return (int)Database::connection()->lastInsertId(); // 新文章编号由同一个连接读取，跳转不会选中其他人的文章。
    }

    /** 管理员编辑时按编号取原始文章，公开阅读入口调用 forReader。 */
    public static function forEditor(int $id): ?array
    {
        return Database::rows('SELECT * FROM page WHERE aid=?', [$id])[0] ?? null;
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
        [$scope, $values] = self::visibility(); // 文章目录与直达阅读页共享同一组阅读条件。
        $sql = "SELECT pg.*, pg.`group` AS gid, g.gname, u.nickname, u.email, u.realname FROM page pg
            LEFT JOIN groups g ON g.gid=pg.`group` JOIN userinfo u ON u.uid=pg.uid WHERE $scope";
        if ($key !== '') { // 关键词仅追加在读者可见的范围内。
            $sql .= " AND (pg.text LIKE ? OR pg.title LIKE ?)";
            array_push($values, '%'.$key.'%', '%'.$key.'%'); // 标题和正文共用同一搜索词，各占一个绑定参数。
        }
        return Database::rows($sql." ORDER BY pg.aid DESC", $values);
    }
}
