<?php
/**
 * 平时提交按题目的当前阅读范围展示。网页用 Submission::forReader(42) 取结果，
 * 用返回的 can_read_source 决定是否展示代码。评测写入、重判和统计使用原始记录。
 */
final class Submission
{
    /** 重判只替换指定 SID 的成绩和耗时，保留原始提交者、文件名与提交时间。由 saveResult 在事务内调用。 */
    public static function rejudge($result, $score, $memory, $accepted, $runtime, $id)
    {
        return Database::execute(
            "UPDATE submit SET result=? , score=? , memory=? , accepted=? , runtime=? WHERE sid=?",
            [$result, $score, $memory, $accepted, $runtime, $id]
        );
    }

    /** 写入首次评测结果。$filename 是用户源码目录里的文件名，$time 是提交的 Unix 秒数。 */
    public static function record($problemId, $userId, $language, $result, $score, $memory, $accepted, $time, $ip, $runtime, $filename)
    {
        return Database::execute(
            "INSERT INTO submit(pid, uid, lang, result, score, memory, accepted, subtime, IP, runtime, srcname) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$problemId, $userId, $language, $result, $score, $memory, $accepted, $time, $ip, $runtime, $filename]
        );
    }

    /** 在已获准阅读的题目页取该用户的最高分提交。返回零或一行，尚未作答时为空数组。 */
    public static function best($problemId, $userId)
    {
        return Database::rows(
            "SELECT * FROM submit WHERE pid =? AND uid =? ORDER BY score DESC LIMIT 1",
            [$problemId, $userId]
        );
    }

    /** 首页最新成绩先筛出可读题目与最低得分，再取最新 $limit 条，隐藏题目不会挤走可见条目。 */
    public static function highScores($minimum, $limit)
    {
        [$scope, $values] = Problem::visibility();
        return Database::rows("SELECT s.sid, s.pid, s.uid, s.result, s.score, s.accepted, s.subtime,
                p.probname, u.nickname, u.realname, u.email, u.memo
            FROM submit s JOIN problem p ON p.pid=s.pid JOIN userinfo u ON u.uid=s.uid
            WHERE $scope AND s.score >= ? ORDER BY s.sid DESC LIMIT ?",
            array_merge($values, [$minimum, max(0, (int)$limit)]));
    }

    /** 供已获准阅读的题目页显示单题排行。分数相同的提交先比耗时，再比内存。 */
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

    // 先按 Problem::visibility() 筛出题目，再判断能否看程序。公开分享或做过该题
    // 只放行代码；换组或题目下架后，旧评论和旧提交不会继续授予阅读题目的权限。
    private static function sourceVisibility(): array
    {
        if (Privilege::allows('查看代码')) return ['1=1', []];
        $shared = "EXISTS (SELECT 1 FROM comments c WHERE c.pid=s.pid AND c.uid=s.uid AND c.showcode=1)";
        $viewer = (int)$_SESSION['ID'];
        if (!$viewer) return [$shared, []];
        return ["(s.uid=? OR $shared OR EXISTS
            (SELECT 1 FROM submit passed WHERE passed.uid=? AND passed.pid=s.pid AND passed.accepted=1))",
            [$viewer, $viewer]];
    }

    /** 省略编号时直接在可读结果中取最新一条。隐藏和不存在的记录都返回 null。 */
    public static function forReader(?int $id = null): ?array
    {
        [$scope, $scopeValues] = Problem::visibility();
        [$source, $sourceValues] = self::sourceVisibility();
        $values = array_merge($sourceValues, $scopeValues);
        $sql = "SELECT s.*, u.nickname, u.realname, p.probname, ($source) AS can_read_source
                FROM submit s JOIN userinfo u ON u.uid=s.uid JOIN problem p ON p.pid=s.pid WHERE $scope";
        if ($id !== null) {
            $sql .= " AND s.sid=?";
            $values[] = $id;
        }
        return Database::rows($sql." ORDER BY s.sid DESC LIMIT 1", $values)[0] ?? null;
    }

    /** ZIP 用数据库里的提交记录选文件。运行目录可能还有编译失败或清理前的临时文件。 */
    public static function exportableNames(int $owner): array
    {
        [$scope, $scopeValues] = Problem::visibility();
        [$source, $sourceValues] = self::sourceVisibility();
        $rows = Database::rows("SELECT DISTINCT s.srcname FROM submit s JOIN problem p ON p.pid=s.pid
            WHERE s.uid=? AND $scope AND ($source)", array_merge([$owner], $scopeValues, $sourceValues));
        return array_column($rows, 'srcname');
    }

    /** 仅定位指定用户目录里实际存在的普通文件。调用前须完成授权；旧记录丢失或路径含链接时返回 null。 */
    public static function sourcePath(string $root, int $owner, string $name): ?string
    {
        if ($name === '' || basename($name) !== $name || str_contains($name, '\\') || str_contains($name, "\0")) return null;
        $folder = rtrim($root, '/').'/'.$owner;
        $file = $folder.'/'.$name;
        return !is_link($folder) && !is_link($file) && is_file($file) ? $file : null;
    }

    /** 重判时按 SID 读取原始记录。调用方须持有管理评测权限且核对 PID；面向读者的详情调用 forReader。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM submit WHERE sid=?",
            [$id]
        );
    }

    /** 个人主页按题号和成绩展示历史，仅返回访问者能阅读的题目。统计次数来自这批记录。 */
    public static function history($userId)
    {
        [$scope, $values] = Problem::visibility();
        return Database::rows("SELECT p.pid, p.probname, s.accepted, s.lang, s.sid FROM submit s
            JOIN problem p ON s.pid=p.pid WHERE $scope AND s.uid=? ORDER BY p.pid, s.score DESC",
            array_merge($values, [$userId]));
    }

    /** 提交列表可按用户、题号和通过状态筛选。$limit 为 null 时读取供分页的全部可见记录。 */
    public static function search($onlyAccepted, $userId, $problemId, $limit)
    {
        [$scope, $values] = Problem::visibility();
        $sql = "SELECT s.*, u.nickname, u.email, u.realname, p.probname FROM submit s
                JOIN userinfo u ON u.uid=s.uid JOIN problem p ON p.pid=s.pid WHERE $scope";
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

    /** 为题库及首页的可见题目提供本人答题图标，返回以 PID 为键的最高分记录，同分取最新 SID。 */
    public static function bestByProblem($userId): array
    {
        if (!$userId) return [];
        $rows = Database::rows("SELECT s.pid, s.sid, s.accepted FROM submit s
            JOIN (SELECT pid, MAX(score) AS score FROM submit WHERE uid=? GROUP BY pid) best
              ON best.pid=s.pid AND best.score=s.score WHERE s.uid=? ORDER BY s.sid", [$userId, $userId]);
        return array_column($rows, null, 'pid');
    }


    /** 在一次事务中保存逐点结果并重算题目、用户统计。$info['rejudge'] 表示复用原 SID，普通提交生成新记录。 */
    public static function saveResult(array $info, $result, $score, $memory, $accepted, $runtime, $src): void
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            // 同一用户或同一题目可能同时完成多份提交。先锁用户、再锁题目，重判也遵守同样的顺序。
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

    /** 新上传的源文件要等评测结束后才有记录，仅清理一天前仍无提交记录的文件。返回已删除的相对路径。 */
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
