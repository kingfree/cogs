<?php
// 平时提交的来源、评测结果和历史。例如 Submission::bySource($userId, $filename)。
final class Submission
{
    public static function bySource($userId, $filename)
    {
        return Database::rows(
            "SELECT * FROM submit WHERE uid=? AND srcname=?",
            [$userId, $filename]
        );
    }

    public static function rejudge($result, $score, $memory, $accepted, $runtime, $id)
    {
        return Database::execute(
            "UPDATE submit SET result=? , score=? , memory=? , accepted=? , runtime=? WHERE sid=?",
            [$result, $score, $memory, $accepted, $runtime, $id]
        );
    }

    public static function record($problemId, $userId, $language, $result, $score, $memory, $accepted, $time, $ip, $runtime, $filename)
    {
        return Database::execute(
            "INSERT INTO submit(pid, uid, lang, result, score, memory, accepted, subtime, IP, runtime, srcname) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$problemId, $userId, $language, $result, $score, $memory, $accepted, $time, $ip, $runtime, $filename]
        );
    }

    public static function best($problemId, $userId)
    {
        return Database::rows(
            "SELECT * FROM submit WHERE pid =? AND uid =? ORDER BY score DESC LIMIT 1",
            [$problemId, $userId]
        );
    }

    public static function highScores($minimum, $limit)
    {
        return Database::rows(
            "SELECT submit.sid, submit.pid, submit.uid, submit.result, submit.score, submit.accepted, submit.subtime, problem.probname, userinfo.nickname, userinfo.realname, userinfo.email, userinfo.memo
             FROM submit, problem, userinfo
             WHERE submit.score>=? AND submit.uid=userinfo.uid AND submit.pid=problem.pid
             ORDER BY submit.sid DESC LIMIT ?",
            [$minimum, $limit]
        );
    }

    public static function leaders($problemId, $limit)
    {
        return Database::rows(
            "SELECT submit.*, userinfo.nickname, userinfo.realname, userinfo.uid, userinfo.email
             FROM submit, userinfo
             WHERE submit.pid=? AND submit.uid=userinfo.uid
             ORDER BY score DESC, runtime ASC, memory ASC LIMIT ?",
            [$problemId, $limit]
        );
    }

    public static function lastId()
    {
        return Database::rows(
            "SELECT max(sid) AS sid FROM submit"
        );
    }

    public static function withAuthor($id)
    {
        return Database::rows(
            "SELECT submit.*, userinfo.nickname, userinfo.realname, submit.subtime, problem.probname, problem.filename
             FROM submit, userinfo, problem
             WHERE submit.pid=problem.pid AND submit.uid=userinfo.uid AND submit.sid=?",
            [$id]
        );
    }

    public static function solved($userId, $problemId)
    {
        return Database::rows(
            "SELECT accepted FROM submit WHERE uid=? AND pid=? ORDER BY score DESC LIMIT 1",
            [$userId, $problemId]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM submit WHERE sid=?",
            [$id]
        );
    }

    public static function history($userId)
    {
        return Database::rows(
            "SELECT problem.pid, problem.probname, submit.accepted, submit.lang, submit.sid
             FROM submit, problem
             WHERE submit.uid=? AND submit.pid=problem.pid
             ORDER BY problem.pid ASC, submit.score DESC",
            [$userId]
        );
    }

    public static function search($readLevel, $onlyAccepted, $userId, $problemId, $limit)
    {
        $sql = "SELECT s.*, u.nickname, u.email, u.realname, p.probname FROM submit s
                JOIN userinfo u ON u.uid=s.uid JOIN problem p ON p.pid=s.pid WHERE p.readforce<=?";
        $values = [$readLevel];
        if ($onlyAccepted) $sql .= " AND s.accepted=1";
        foreach (['uid' => $userId, 'pid' => $problemId] as $column => $id) {
            if ($id) {
                $sql .= " AND s.$column=?";
                $values[] = $id;
            }
        }
        $sql .= " ORDER BY s.sid DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $values[] = (int)$limit;
        }
        return Database::rows($sql, $values);
    }

    public static function bestByProblem($userId): array
    {
        if (!$userId) return [];
        $rows = Database::rows("SELECT s.pid, s.sid, s.accepted FROM submit s
            JOIN (SELECT pid, MAX(score) AS score FROM submit WHERE uid=? GROUP BY pid) best
              ON best.pid=s.pid AND best.score=s.score WHERE s.uid=? ORDER BY s.sid", [$userId, $userId]);
        return array_column($rows, null, 'pid');
    }


    public static function saveResult(array $info, $result, $score, $memory, $accepted, $runtime, $src): void
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            Database::statement("SELECT uid FROM userinfo WHERE uid=? FOR UPDATE", [$info['uid']]);
            Database::statement("SELECT pid FROM problem WHERE pid=? FOR UPDATE", [$info['pid']]);
            if (!empty($info['rejudge'])) {
                self::rejudge($result, $score, $memory, $accepted, $runtime, $info['sid']);
            } else {
                self::record($info['pid'], $info['uid'], $info['language'], $result, $score, $memory,
                    $accepted, time(), $_SERVER['REMOTE_ADDR'], $runtime, $src);
            }
            Database::execute("UPDATE problem SET submitcnt=(SELECT COUNT(*) FROM submit WHERE pid=?),
                acceptcnt=(SELECT COUNT(DISTINCT uid) FROM submit WHERE pid=? AND accepted=1),
                lastacid=COALESCE((SELECT uid FROM submit WHERE pid=? AND accepted=1 ORDER BY sid DESC LIMIT 1),lastacid)
                WHERE pid=?", array_fill(0, 4, $info['pid']));
            Ranking::recalculate($info['uid']);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollBack();
            throw $error;
        }
    }

    // 新上传的源文件要等评测结束后才有记录；仅清理一天前的孤立文件。
    public static function removeOrphans(string $root): array
    {
        $removed = [];
        foreach (new FilesystemIterator($root) as $folder) {
            if (!$folder->isDir() || $folder->isLink() || !ctype_digit($folder->getFilename())) continue;
            $uid = (int)$folder->getFilename();
            $records = Database::rows('SELECT srcname FROM submit WHERE uid=?', [$uid]);
            $recorded = array_fill_keys(array_column($records, 'srcname'), true);
            foreach (new FilesystemIterator($folder->getPathname()) as $file) {
                if (!$file->isFile() || $file->isLink() || $file->getMTime() > time()-86400 || isset($recorded[$file->getFilename()])) continue;
                if (unlink($file->getPathname())) $removed[] = $uid.'/'.$file->getFilename();
            }
        }
        return $removed;
    }

}
