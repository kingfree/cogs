<?php
/**
 * 题目和文章共用评论表，pid 指向题目，aid 指向文章。讨论列表按读者能查看的原文筛选。
 * 例：$comments = Comment::search('', 1, 0, 0, null);  // 阅读 1 号题的讨论。
 */
final class Comment
{
    /** 把题解发布为题目评论，默认不开放提交代码。 */
    public static function addSolution($problemId, $author, $detail, $time)
    {
        return Database::execute(
            "INSERT INTO comments(pid, uid, detail, stime, showcode) VALUES(?, ?, ?, ?,0)",
            [$problemId, $author, $detail, $time]
        );
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
    public static function search($key, $problemId, $pageId, $userId, $limit)
    {
        // 评论属于题目或文章，跨全站搜索时也要把所属原文的阅读条件加到查询中。
        [$problemScope, $problemValues] = Problem::visibility();
        [$pageScope, $pageValues] = Page::visibility();
        $sql = "SELECT c.*, (SELECT s.sid FROM submit s WHERE s.pid=c.pid AND s.uid=c.uid ORDER BY s.subtime DESC, s.sid DESC LIMIT 1) AS latest_sid, (SELECT s.result FROM submit s WHERE s.pid=c.pid AND s.uid=c.uid ORDER BY s.subtime DESC, s.sid DESC LIMIT 1) AS latest_result, u.nickname, u.realname, u.email, u.accepted, u.submited, u.grade, u.memo
                FROM comments c JOIN userinfo u ON u.uid=c.uid WHERE
                (EXISTS (SELECT 1 FROM problem p WHERE p.pid=c.pid AND $problemScope)
                OR EXISTS (SELECT 1 FROM page pg WHERE pg.aid=c.aid AND $pageScope))";
        $values = array_merge($problemValues, $pageValues);
        if ($key !== '') {
            $sql .= " AND (c.detail LIKE ? OR u.nickname LIKE ? OR u.usr LIKE ? OR u.realname LIKE ?)";
            array_push($values, ...array_fill(0, 4, '%'.$key.'%'));
        }
        foreach (['pid' => $problemId, 'aid' => $pageId, 'uid' => $userId] as $column => $id) {
            if ($id) {
                $sql .= " AND c.$column=?";
                $values[] = $id;
                break;
            }
        }
        // 文章内按楼层编号读对话；全站列表优先显示最近更新。
        $sql .= $problemId || $pageId ? " ORDER BY c.cid" : " ORDER BY c.stime DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $values[] = (int)$limit;
        }
        return Database::rows($sql, $values);
    }
}
