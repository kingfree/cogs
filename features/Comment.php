<?php
// 题目和页面讨论。例如 Comment::byId(1)。
final class Comment
{
    public static function addSolution($problemId, $author, $detail, $time)
    {
        return Database::execute(
            "INSERT INTO comments(pid, uid, detail, stime, showcode) VALUES(?, ?, ?, ?,0)",
            [$problemId, $author, $detail, $time]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT comments.* FROM comments WHERE comments.cid=? LIMIT 1",
            [$id]
        );
    }

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

    public static function edit($detail, $time, $shareCode, $id, $author)
    {
        return Database::execute(
            "UPDATE comments SET detail=?, stime=? , showcode=? WHERE cid=? AND uid=?",
            [$detail, $time, $shareCode, $id, $author]
        );
    }

    public static function onProblem($problemId, $author, $detail, $time, $shareCode)
    {
        return Database::execute(
            "INSERT INTO comments(pid, uid, detail, stime, showcode) VALUES(?, ?, ?, ? , ?)",
            [$problemId, $author, $detail, $time, $shareCode]
        );
    }

    public static function onPage($pageId, $author, $detail, $time, $shareCode)
    {
        return Database::execute(
            "INSERT INTO comments(aid, uid, detail, stime, showcode) VALUES(?, ?, ?, ? , ?)",
            [$pageId, $author, $detail, $time, $shareCode]
        );
    }

    public static function codeSharing($userId, $problemId)
    {
        return Database::rows(
            "SELECT showcode FROM comments WHERE uid=? AND pid=? ORDER BY showcode DESC LIMIT 1",
            [$userId, $problemId]
        );
    }

    public static function search($key, $problemId, $pageId, $userId, $limit)
    {
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
        $sql .= $problemId || $pageId ? " ORDER BY c.cid" : " ORDER BY c.stime DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $values[] = (int)$limit;
        }
        return Database::rows($sql, $values);
    }
}
