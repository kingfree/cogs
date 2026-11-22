<?php
// 比赛提交、评测结果和成绩单。例如 ContestEntry::withAuthor(1)。
final class ContestEntry
{
    public static function withAuthor($id)
    {
        return Database::rows(
            "SELECT compbase.cname, problem.pid, problem.filename, problem.probname, userinfo.uid, userinfo.nickname, userinfo.realname, comptime.endtime, compscore.*
             FROM problem, compscore, comptime, compbase, userinfo
             WHERE compscore.pid=problem.pid AND comptime.ctid=compscore.ctid AND userinfo.uid=compscore.uid AND compscore.csid=? AND compbase.cbid=comptime.cbid",
            [$id]
        );
    }

    public static function forJudging($id)
    {
        return Database::rows(
            "SELECT compscore.lang, compscore.uid, problem.filename, compscore.ctid, problem.pid, problem.datacnt, problem.timelimit, problem.memorylimit, problem.plugin
             FROM compscore, problem
             WHERE compscore.pid=problem.pid AND compscore.csid=?",
            [$id]
        );
    }

    public static function byEntrant($userId, $roundId)
    {
        return Database::rows(
            "SELECT compscore.csid, compscore.pid, compscore.lang, compscore.subtime, compscore.score, compscore.result, problem.probname
             FROM compscore, problem
             WHERE problem.pid=compscore.pid AND compscore.uid=? AND compscore.ctid=?",
            [$userId, $roundId]
        );
    }

    public static function sourceFiles($roundId)
    {
        return Database::rows(
            "SELECT compscore.uid, userinfo.realname, userinfo.nickname, problem.filename
             FROM compscore, userinfo, problem
             WHERE userinfo.uid=compscore.uid AND compscore.pid=problem.pid AND compscore.ctid=?
             ORDER BY uid ASC",
            [$roundId]
        );
    }

    public static function standings($problemId, $roundId)
    {
        return Database::rows(
            "SELECT *
             FROM compscore, userinfo
             WHERE userinfo.uid=compscore.uid AND compscore.pid=? AND compscore.ctid=?
             ORDER BY compscore.score DESC, compscore.runtime ASC, compscore.memory ASC",
            [$problemId, $roundId]
        );
    }

    public static function entrantResult($userId, $owner, $problemId, $roundId)
    {
        return Database::rows(
            "SELECT *
             FROM compscore, userinfo
             WHERE compscore.uid=? AND userinfo.uid=? AND compscore.pid=? AND compscore.ctid=?",
            [$userId, $owner, $problemId, $roundId]
        );
    }

    public static function lookup($userId, $problemId, $roundId)
    {
        return Database::rows(
            "SELECT csid FROM compscore WHERE uid=? AND pid=? AND ctid=?",
            [$userId, $problemId, $roundId]
        );
    }

    public static function replaceSource($time, $language, $roundId, $userId, $problemId)
    {
        return Database::execute(
            "UPDATE compscore SET score=0, result=NULL, memory=0, runtime=0, subtime=?, lang=? WHERE ctid=? AND uid=? AND pid=?",
            [$time, $language, $roundId, $userId, $problemId]
        );
    }

    public static function create($roundId, $userId, $problemId, $time, $language)
    {
        return Database::execute(
            "INSERT INTO compscore(ctid, uid, pid, subtime, lang) VALUES(?, ?, ?, ?, ?)",
            [$roundId, $userId, $problemId, $time, $language]
        );
    }

    public static function saveScore($score, $result, $memory, $runtime, $id)
    {
        return Database::execute(
            "UPDATE compscore SET score=?, result=?, memory=?, runtime=? WHERE csid=?",
            [$score, $result, $memory, $runtime, $id]
        );
    }

    public static function judgingQueue(array $ids)
    {
        if (!$ids) return [];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        return Database::rows("SELECT e.*, u.nickname, u.realname, p.probname, p.filename FROM compscore e
            JOIN userinfo u ON u.uid=e.uid JOIN problem p ON p.pid=e.pid WHERE e.csid IN ($marks)
            ORDER BY e.csid", $ids);
    }

    public static function submitSource($round, $problem, $user, $name, $language, $upload, $root)
    {
        $db = Database::connection();
        $dir = rtrim($root, '/')."/$round/$user";
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) throw new RuntimeException('不能创建交卷目录。');
        $path = $dir.'/'.$name.'.'.Language::TYPES[$language][0];
        $backup = null;
        $installed = false;
        $db->beginTransaction();
        try {
            Database::statement("SELECT ctid FROM comptime WHERE ctid=? FOR UPDATE", [$round]);
            $entry = self::lookup($user, $problem, $round)[0] ?? null;
            if ($entry) {
                $id = $entry['csid'];
                self::replaceSource(time(), $language, $round, $user, $problem);
            } else {
                self::create($round, $user, $problem, time(), $language);
                $id = (int)$db->lastInsertId();
            }
            if (is_file($path)) {
                $backup = $path.'.'.bin2hex(random_bytes(8));
                if (!rename($path, $backup)) throw new RuntimeException('不能保存上次提交。');
            }
            if (!move_uploaded_file($upload, $path)) throw new RuntimeException('交卷文件写入失败。');
            $installed = true;
            $db->commit();
        } catch (Throwable $error) {
            $db->rollBack();
            if ($installed) unlink($path);
            if ($backup && is_file($backup)) rename($backup, $path);
            throw $error;
        }
        if ($backup) unlink($backup);
        return $id;
    }

    public static function roundResults($id): array
    {
        $entries = Database::rows("SELECT e.*, u.nickname, u.realname, u.email, p.probname FROM compscore e
            JOIN userinfo u ON u.uid=e.uid JOIN problem p ON p.pid=e.pid WHERE e.ctid=? ORDER BY e.uid, e.pid", [$id]);
        $users = [];
        foreach ($entries as $entry) {
            $uid = $entry['uid'];
            if (!isset($users[$uid])) $users[$uid] = ['user'=>$entry, 'marks'=>[], 'total'=>0];
            $users[$uid]['marks'][$entry['pid']] = $entry;
            $users[$uid]['total'] += $entry['score'];
        }
        return $users;
    }

}
