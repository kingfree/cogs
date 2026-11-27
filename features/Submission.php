<?php
/**
 * 平时提交按题目的当前阅读范围展示。网页用 Submission::forReader(42) 取结果，
 * 用返回的 can_read_source 决定是否展示代码。评测写入、重判和统计使用原始记录。
 */
final class Submission
{
    /** 重判只替换指定 SID 的成绩和耗时，保留原始提交者、文件名与提交时间。由 saveResult 在事务内调用。 */
    private static function rejudge($result, $score, $memory, $accepted, $runtime, $id)
    {
        return Database::execute(
            "UPDATE submit SET result=? , score=? , memory=? , accepted=? , runtime=? WHERE sid=?",
            [$result, $score, $memory, $accepted, $runtime, $id]
        );
    }

    /** 写入首次评测结果。$filename 是用户源码目录里的文件名，$time 是提交的 Unix 秒数。 */
    private static function record($problemId, $userId, $language, $result, $score, $memory, $accepted, $time, $ip, $runtime, $filename)
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
        if (Privilege::allows('查看代码')) return ['1=1', []];   // 全站读码授权仍受外层题目阅读范围约束。
        $shared = "EXISTS (SELECT 1 FROM comments c WHERE c.pid=s.pid AND c.uid=s.uid AND c.showcode=1)";
        $viewer = (int)$_SESSION['ID'];   // 身份取自会话，不能由查看链接指定。
        if (!$viewer) return [$shared, []];   // 游客只能读作者已分享的源码。
        return ["(s.uid=? OR $shared OR EXISTS
            (SELECT 1 FROM submit passed WHERE passed.uid=? AND passed.pid=s.pid AND passed.accepted=1))",
            [$viewer, $viewer]];
    }

    /** 省略编号时直接在可读结果中取最新一条。隐藏和不存在的记录都返回 null。 */
    public static function forReader(?int $id = null): ?array
    {
        [$scope, $scopeValues] = Problem::visibility();
        [$source, $sourceValues] = self::sourceVisibility();
        $values = array_merge($sourceValues, $scopeValues);   // 先绑定 SELECT 里的源码授权，再绑定 WHERE 中的可见范围。
        $sql = "SELECT s.*, u.nickname, u.realname, p.probname, ($source) AS can_read_source
                FROM submit s JOIN userinfo u ON u.uid=s.uid JOIN problem p ON p.pid=s.pid WHERE $scope";
        if ($id !== null) {
            $sql .= " AND s.sid=?";   // 显式编号也要与可见范围取交集。
            $values[] = $id;
        }
        return Database::rows($sql." ORDER BY s.sid DESC LIMIT 1", $values)[0] ?? null;
    }

    /** ZIP 用数据库里的提交记录选文件。运行目录可能还有编译失败或清理前的临时文件。 */
    public static function exportableNames(int $owner): array
    {
        [$scope, $scopeValues] = Problem::visibility();
        [$source, $sourceValues] = self::sourceVisibility();
        // 导出和详情使用同一份源码授权，同时从用户自己的记录中去重历史文件名。
        $rows = Database::rows("SELECT DISTINCT s.srcname FROM submit s JOIN problem p ON p.pid=s.pid
            WHERE s.uid=? AND $scope AND ($source)", array_merge([$owner], $scopeValues, $sourceValues));
        return array_column($rows, 'srcname');   // 压缩包只接收筛选后的文件名。
    }

    /** 仅定位指定用户目录里实际存在的普通文件。调用前须完成授权；旧记录丢失或路径含链接时返回 null。 */
    public static function sourcePath(string $root, int $owner, string $name): ?string
    {
        if ($name === '' || basename($name) !== $name || str_contains($name, '\\') || str_contains($name, "\0")) return null;
        $folder = rtrim($root, '/').'/'.$owner;   // 父目录由已授权提交的作者编号决定。
        $file = $folder.'/'.$name;   // 沿用数据库的完整历史文件名。
        return !is_link($folder) && !is_link($file) && is_file($file) ? $file : null;
    }

    /** 为源码详情返回 UTF-8 文本；参数是 forReader() 已校验的记录，无源码权限、文件遗失或 ZIP 时返回空串。 */
    public static function sourceText(string $root, array $submission): string
    {
        if (!$submission['can_read_source'] || (int)$submission['lang'] === 3) return '';
        $file = self::sourcePath($root, (int)$submission['uid'], $submission['srcname']);  // 目录和名称必须与登记记录吻合。
        if ($file === null) return '';
        $text = file_get_contents($file);                                                // 老站保存的源码可能仍是 GBK。
        if ($text === false) return '';
        return mb_check_encoding($text, 'UTF-8') ? $text : mb_convert_encoding($text, 'UTF-8', 'GBK');
    }

    /** 为已获准管理评测的入口找出指定题目的旧提交。记录与题号不符时返回 null。 */
    public static function forRejudge(int $id, int $problemId): ?array
    {
        return Database::rows('SELECT sid, uid, lang, srcname FROM submit WHERE sid=? AND pid=?', [$id, $problemId])[0] ?? null;
    }

    /** 个人主页按题号和成绩展示历史，仅返回访问者能阅读的题目。统计次数来自这批记录。 */
    public static function history($userId)
    {
        [$scope, $values] = Problem::visibility();
        return Database::rows("SELECT p.pid, p.probname, s.accepted, s.lang, s.sid FROM submit s
            JOIN problem p ON s.pid=p.pid WHERE $scope AND s.uid=? ORDER BY p.pid, s.score DESC",
            array_merge($values, [$userId]));
    }

    /** 返回当前页记录和可见总数。默认首页只取半页最新成绩；完整列表由数据库按筛选条件分页。 */
    public static function page(bool $onlyAccepted, int $userId, int $problemId, int $page, int $size, bool $paginated): array
    {
        [$scope, $values] = Problem::visibility();                           // 每次翻页都按当前读者重新判断题目可见性。
        $from = " FROM submit s JOIN userinfo u ON u.uid=s.uid JOIN problem p ON p.pid=s.pid WHERE $scope";
        if ($onlyAccepted) $from .= " AND s.accepted=1";                     // “通过”不会包含部分分。
        if ($userId) {
            $from .= " AND s.uid=?";                                     // 选手筛选会同时影响总数和当前页。
            $values[] = $userId;                                             // 选手编号作为绑定值，不能改变查询结构。
        }
        if ($problemId) {
            $from .= " AND s.pid=?";
            $values[] = $problemId;                                      // 与前面的选手条件按相同顺序绑定。
        }
        $size = max(1, $size);
        $limit = $paginated ? $size : max(1, intdiv($size, 2));              // 近期预览保留旧站的半页条数。
        $total = $paginated ? (int)Database::rows("SELECT COUNT(*) AS total".$from, $values)[0]['total'] : null;   // 只在完整列表统计总行数，预览不必扫描全部结果。
        if ($page < 1 || $page > max(1, (int)ceil(($total ?? $limit)/$limit))) throw new OutOfBoundsException('页面错误！');
        $rows = Database::rows("SELECT s.*, u.nickname, u.email, u.realname, p.probname".$from.
            " ORDER BY s.sid DESC LIMIT ? OFFSET ?", array_merge($values, [$limit, ($page-1)*$limit]));
        return [$rows, $total ?? count($rows)];                              // 页面只持有将要展示的记录。
    }

    /** 为题库及首页的可见题目提供本人答题图标，返回以 PID 为键的最高分记录，同分取最新 SID。 */
    public static function bestByProblem($userId): array
    {
        if (!$userId) return [];   // 游客没有提交进度标记。
        $rows = Database::rows("SELECT s.pid, s.sid, s.accepted FROM submit s
            JOIN (SELECT pid, MAX(score) AS score FROM submit WHERE uid=? GROUP BY pid) best
              ON best.pid=s.pid AND best.score=s.score WHERE s.uid=? ORDER BY s.sid", [$userId, $userId]);
        return array_column($rows, null, 'pid');   // 同题并列时查询已按 SID 排序，后面的新记录覆盖旧键。
    }


    /** 在一次事务中保存逐点结果并重算题目、用户统计。$info['rejudge'] 表示复用原 SID，普通提交生成新记录。 */
    public static function saveResult(array $info, $result, $score, $memory, $accepted, $runtime, $src): int
    {
        $db = Database::connection();
        $db->beginTransaction();   // 提交、题目统计和排行榜一起生效。
        try {
            // 同一用户或同一题目可能同时完成多份提交。先锁用户、再锁题目，重判也遵守同样的顺序。
            Database::statement("SELECT uid FROM userinfo WHERE uid=? FOR UPDATE", [$info['uid']]);
            Database::statement("SELECT pid FROM problem WHERE pid=? FOR UPDATE", [$info['pid']]);   // 锁住该题，其他选手同时交题也不会漏计。
            if (!empty($info['rejudge'])) {
                $submissionId = (int)$info['sid'];
                self::rejudge($result, $score, $memory, $accepted, $runtime, $submissionId);   // 更新同一记录，保留首次提交时间。
            } else {
                self::record($info['pid'], $info['uid'], $info['language'], $result, $score, $memory,
                    $accepted, time(), $_SERVER['REMOTE_ADDR'], $runtime, $src);
                $submissionId = (int)$db->lastInsertId();   // 必须在后面的统计查询前取新 SID。
            }
            Database::execute("UPDATE problem SET submitcnt=(SELECT COUNT(*) FROM submit WHERE pid=?),
                acceptcnt=(SELECT COUNT(DISTINCT uid) FROM submit WHERE pid=? AND accepted=1),
                lastacid=COALESCE((SELECT uid FROM submit WHERE pid=? AND accepted=1 ORDER BY sid DESC LIMIT 1),lastacid)
                WHERE pid=?", array_fill(0, 4, $info['pid']));
            Ranking::recalculate($info['uid']);   // 用更新后的提交重算原作者的练习积分。
            $db->commit();   // 三个更新都成功后，记录链接才能对外使用。
            return $submissionId;
        } catch (Throwable $error) {
            $db->rollBack();   // 任一步失败，保留上一次完整记录和统计。
            throw $error;
        }
    }

    /** 新上传的源文件要等评测结束后才有记录，仅清理一天前仍无提交记录的文件。返回已删除的相对路径。 */
    public static function removeOrphans(string $root): array
    {
        $removed = [];
        foreach (new FilesystemIterator($root) as $folder) {
            if (!$folder->isDir() || $folder->isLink() || !ctype_digit($folder->getFilename())) continue;   // 只遍历真实的数字用户目录。
            $uid = (int)$folder->getFilename();
            $records = Database::rows('SELECT srcname FROM submit WHERE uid=?', [$uid]);
            $recorded = array_fill_keys(array_column($records, 'srcname'), true);   // 有提交记录的源码必须留作查看和重判。
            foreach (new FilesystemIterator($folder->getPathname()) as $file) {
                if (!$file->isFile() || $file->isLink() || $file->getMTime() > time()-86400 || isset($recorded[$file->getFilename()])) continue;
                if (unlink($file->getPathname())) $removed[] = $uid.'/'.$file->getFilename();   // 仅列出实际删除成功的旧文件。
            }
        }
        return $removed;
    }

}
