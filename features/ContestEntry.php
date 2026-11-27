<?php
/**
 * 一份比赛交卷由场次、参赛人和题目共同确定，同题重交沿用原来的交卷编号 csid。
 * contest/submit.php 核对参赛资格后调用 submitSource；contest/compile.php 按 csid 回写分数。
 * 成绩页从 ContestRound::forReader($id) 取得可见题号，再传给 roundResults($id, $problemIds)。
 */
final class ContestEntry
{
    /** 按 csid 定位作者与题目，打开源码前由 ContestRound::forReader 校验场次与题目。 */
    public static function withAuthor($id)
    {
        return Database::rows(
            "SELECT problem.filename, problem.probname, userinfo.nickname, compscore.*
             FROM compscore JOIN problem ON problem.pid=compscore.pid JOIN userinfo ON userinfo.uid=compscore.uid
             WHERE compscore.csid=?",
            [$id]
        );
    }

    /** 从已保存交卷读取语言及题目限制，重判使用服务器记录的参数。 */
    public static function forJudging($id)
    {
        return Database::rows(
            "SELECT compscore.lang, compscore.uid, problem.filename, compscore.ctid, problem.pid, problem.datacnt, problem.timelimit, problem.memorylimit, problem.plugin
             FROM compscore, problem
             WHERE compscore.pid=problem.pid AND compscore.csid=?",
            [$id]
        );
    }

    /** 为场次导出提供用户编号和题目文件名，源码内容仍从交卷目录读取。 */
    public static function sourceFiles($roundId)
    {
        return Database::rows(
            "SELECT compscore.uid, compscore.lang, problem.filename
             FROM compscore, userinfo, problem
             WHERE userinfo.uid=compscore.uid AND compscore.pid=problem.pid AND compscore.ctid=?
             ORDER BY uid ASC",
            [$roundId]
        );
    }

    /** 返回一场比赛中某题的成绩，分数相同时依次按耗时、内存排序。 */
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

    /** 读取单个选手在指定场次、题目的成绩；调用方传同一用户编号匹配选手与资料。 */
    public static function entrantResult($userId, $owner, $problemId, $roundId)
    {
        return Database::rows(
            "SELECT *
             FROM compscore, userinfo
             WHERE compscore.uid=? AND userinfo.uid=? AND compscore.pid=? AND compscore.ctid=?",
            [$userId, $owner, $problemId, $roundId]
        );
    }

    /** 按选手、题目和场次找到已有的交卷编号。 */
    public static function lookup($userId, $problemId, $roundId)
    {
        return Database::rows(
            "SELECT csid FROM compscore WHERE uid=? AND pid=? AND ctid=?",
            [$userId, $problemId, $roundId]
        );
    }

    /** 再次交卷时清掉旧分和资源用量，将这条记录恢复到待评状态。 */
    public static function replaceSource($time, $language, $roundId, $userId, $problemId)
    {
        return Database::execute(
            "UPDATE compscore SET score=0, result=NULL, memory=0, runtime=0, subtime=?, lang=? WHERE ctid=? AND uid=? AND pid=?",
            [$time, $language, $roundId, $userId, $problemId]
        );
    }

    /** 第一次交本题时为该场选手登记一条待评记录。 */
    public static function create($roundId, $userId, $problemId, $time, $language)
    {
        return Database::execute(
            "INSERT INTO compscore(ctid, uid, pid, subtime, lang) VALUES(?, ?, ?, ?, ?)",
            [$roundId, $userId, $problemId, $time, $language]
        );
    }

    /** 将最终得分、逐点结果、内存和耗时写回指定 csid。 */
    public static function saveScore($score, $result, $memory, $runtime, $id)
    {
        return Database::execute(
            "UPDATE compscore SET score=?, result=?, memory=?, runtime=? WHERE csid=?",
            [$score, $result, $memory, $runtime, $id]
        );
    }

    /** 按交卷编号列出管理员选中的待处理记录，空选择直接返回空队列。 */
    public static function judgingQueue(array $ids)
    {
        if (!$ids) return [];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        return Database::rows("SELECT e.*, u.nickname, u.realname, p.probname, p.filename FROM compscore e
            JOIN userinfo u ON u.uid=e.uid JOIN problem p ON p.pid=e.pid WHERE e.csid IN ($marks)
            ORDER BY e.csid", $ids);
    }

    /**
     * 保存已经过参赛资格校验的交卷，数据库和源码替换成功后返回 csid。
     * @param string $upload PHP 接收上传后生成的临时文件路径。
     * @param string $root 站点的比赛源码根目录，各场次、各用户分别存放。
     * @return int|string 首次交卷生成的编号，或重交沿用的编号。
     * @throws RuntimeException 无法建立目录、备份旧文件或移动上传文件。
     */
    public static function submitSource($round, $problem, $user, $name, $language, $upload, $root)
    {
        $db = Database::connection();
        $dir = rtrim($root, '/')."/$round/$user";
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) throw new RuntimeException('不能创建交卷目录。');
        $path = $dir.'/'.$name.'.'.Language::TYPES[$language][0];
        $backup = null;
        $installed = false;
        // 先取得场次锁再判断是否第一次交卷，并发重交才会复用同一条成绩记录。
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
            // 同路径旧源码先改名保留，等成绩记录提交成功才可删除。
            if (is_file($path)) {
                $backup = $path.'.'.bin2hex(random_bytes(8));
                if (!rename($path, $backup)) throw new RuntimeException('不能保存上次提交。');
            }
            if (!move_uploaded_file($upload, $path)) throw new RuntimeException('交卷文件写入失败。');
            // 从这里起，事务失败还需删除刚安装的新文件。
            $installed = true;
            $db->commit();
        } catch (Throwable $error) {
            // 文件系统不随数据库回滚；下面按安装进度恢复旧文件。
            $db->rollBack();
            if ($installed) unlink($path);
            if ($backup && is_file($backup)) rename($backup, $path);
            throw $error;
        }
        if ($backup) unlink($backup);
        return $id;
    }

    /**
     * 一次读完整场交卷以生成成绩表。每名选手的 marks 以题号为键，未交题的键不存在。
     * @return array 用户编号对应 user、marks 和 total，total 为已交各题得分之和。
     */
    public static function roundResults($id, ?array $visibleProblems = null): array
    {
        $query = "SELECT e.*, u.nickname, u.realname, u.email, p.probname FROM compscore e
            JOIN userinfo u ON u.uid=e.uid JOIN problem p ON p.pid=e.pid WHERE e.ctid=?";
        $values = [$id];
        // 管理页取全部交卷。公开成绩表只统计读者能看的题，避免由总分推算隐藏题成绩。
        if ($visibleProblems !== null) {
            if (!$visibleProblems) return [];
            $marks = implode(',', array_fill(0, count($visibleProblems), '?'));
            $query .= " AND e.pid IN ($marks)";
            array_push($values, ...$visibleProblems);
        }
        $entries = Database::rows($query.' ORDER BY e.uid, e.pid', $values);
        $users = [];
        foreach ($entries as $entry) {
            $uid = $entry['uid'];
            if (!isset($users[$uid])) $users[$uid] = ['user'=>$entry, 'marks'=>[], 'total'=>0];
            // 保留每题原始记录，让成绩单能同时显示单题结果和总分。
            $users[$uid]['marks'][$entry['pid']] = $entry;
            $users[$uid]['total'] += $entry['score'];
        }
        return $users;
    }

}
