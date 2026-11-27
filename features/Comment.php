<?php
/**
 * 题目和文章共用评论表，pid 指向题目，aid 指向文章。讨论列表按读者能查看的原文筛选。
 * 例：$comments = Comment::search(problemId: 1);  // 阅读 1 号题的讨论。
 */
final class Comment
{
    /** 把题解发布为题目评论，默认不开放提交代码。 */
    public static function addSolution($problemId, $author, $detail, $time)
    {
        return self::onProblem($problemId, $author, $detail, $time, 0); // 题解与普通题目讨论共用写入，默认不分享代码。
    }

    /** 读取待编辑的评论；修改入口还须核对其作者和原文的可见性。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT comments.* FROM comments WHERE comments.cid=? LIMIT 1",
            [$id]
        );
    }

    /** 在已确认可读的题目详情内，按楼层顺序列出讨论和各作者最近提交的编号。 */
    public static function recentForProblem($problemId)
    {
        return Database::rows(
            "SELECT comments.*, (SELECT s.sid FROM submit s WHERE s.pid=comments.pid AND s.uid=comments.uid ORDER BY s.subtime DESC, s.sid DESC LIMIT 1) AS latest_sid, userinfo.nickname, userinfo.uid, userinfo.email
             FROM comments, userinfo
             WHERE userinfo.uid=comments.uid AND comments.pid=?
             ORDER BY comments.cid ASC",
            [$problemId]
        );
    }

    /** 按评论号和作者同时匹配，其他用户的评论不会被更新。 */
    public static function edit($detail, $time, $shareCode, $id, $author)
    {
        return Database::execute(
            "UPDATE comments SET detail=?, stime=? , showcode=? WHERE cid=? AND uid=?",
            [$detail, $time, $shareCode, $id, $author]
        );
    }

    /** 创建题目讨论，可由作者选择是否分享此题的代码。 */
    public static function onProblem($problemId, $author, $detail, $time, $shareCode)
    {
        return Database::execute(
            "INSERT INTO comments(pid, uid, detail, stime, showcode) VALUES(?, ?, ?, ? , ?)",
            [$problemId, $author, $detail, $time, $shareCode]
        );
    }

    /** 把文章评论关联到 aid，调用入口需先确认该读者可以打开文章。 */
    public static function onPage($pageId, $author, $detail, $time, $shareCode)
    {
        return Database::execute(
            "INSERT INTO comments(aid, uid, detail, stime, showcode) VALUES(?, ?, ?, ? , ?)",
            [$pageId, $author, $detail, $time, $shareCode]
        );
    }

    /** 全站、文章与题目讨论共用此入口。筛选顺序为题号、文章号、作者，limit 为 null 时不截断。 */
    public static function search($key = '', $problemId = 0, $pageId = 0, $userId = 0, $limit = null)
    {
        // 评论属于题目或文章，跨全站搜索时也要把所属原文的阅读条件加到查询中。
        [$problemScope, $problemValues] = Problem::visibility(); // 题目讨论继承所属题目的可见范围。
        [$pageScope, $pageValues] = Page::visibility(); // 文章讨论按文章的阅读等级和分组筛选。
        $sql = "SELECT c.*, (SELECT s.sid FROM submit s WHERE s.pid=c.pid AND s.uid=c.uid ORDER BY s.subtime DESC, s.sid DESC LIMIT 1) AS latest_sid, (SELECT s.result FROM submit s WHERE s.pid=c.pid AND s.uid=c.uid ORDER BY s.subtime DESC, s.sid DESC LIMIT 1) AS latest_result, u.nickname, u.realname, u.email, u.accepted, u.submited, u.grade, u.memo
                FROM comments c JOIN userinfo u ON u.uid=c.uid WHERE
                (EXISTS (SELECT 1 FROM problem p WHERE p.pid=c.pid AND $problemScope)
                OR EXISTS (SELECT 1 FROM page pg WHERE pg.aid=c.aid AND $pageScope))";
        $values = array_merge($problemValues, $pageValues); // 先绑定两类原文的阅读范围，再附加关键词和目标编号。
        if ($key !== '') {
            $pattern = '%'.$key.'%';
            $filter = 'c.detail LIKE ? OR u.nickname LIKE ? OR u.usr LIKE ?'; // 正文、昵称、登录名随评论公开。
            array_push($values, $pattern, $pattern, $pattern);
            if (Privilege::allows('查看用户')) {
                $filter .= ' OR u.realname LIKE ?'; // 隐藏姓名的用户也无法利用搜索结果判断作者姓名。
                $values[] = $pattern;
            }
            $sql .= " AND ($filter)";
        }
        foreach (['pid' => $problemId, 'aid' => $pageId, 'uid' => $userId] as $column => $id) { // 筛选列固定在这三种业务对象内；URL 不能指定数据库列名。
            if ($id) {
                $sql .= " AND c.$column=?";
                $values[] = $id; // 此编号与当前选中的筛选列绑定。
                break; // 同时传多个范围时保留旧页面“题目、文章、作者”的优先级。
            }
        }
        // 文章内按楼层编号读对话；全站列表优先显示最近更新。
        $sql .= $problemId || $pageId ? " ORDER BY c.cid" : " ORDER BY c.stime DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $values[] = (int)$limit; // PDO 将条数作为整数绑定，避免 LIMIT 变成字符串参数。
        }
        return Database::rows($sql, $values);
    }
}
