<?php
/**
 * 一份比赛交卷由场次、参赛人和题目共同确定，同题重交沿用原来的交卷编号 csid。
 * contest/submit.php 调用 submitSource 核对参赛资格并保存源码，contest/compile.php 按 csid 请求重判。
 * 成绩页从 ContestRound::forReader($id) 取得可见题号，再传给 roundResults($id, $problemIds)，由 rankedResults 给已公布成绩的选手排列名次。
 */
final class ContestEntry
{
    /**
     * 比赛源码按场次、选手、题目文件名和语言定位。读、导出和写入都使用这个位置。
     * 旧站点可能留有已删除的文件或失效的语言编号，读取时跳过。
     */
    private static function sourcePath(string $root, int $round, int $user, string $name, int $language): ?string
    {
        $extension = Language::TYPES[$language][0] ?? null; // 用数据库的语言编号取扩展名，禁止客户端另定路径。
        if (!$extension || !TestData::name($name) || $round < 1 || $user < 1) return null;
        $roundDir = rtrim($root, '/').'/'.$round; // 不同场次同名试题的源码各自保存。
        $userDir = $roundDir.'/'.$user; // 每位参赛者在本场有独立目录。
        return is_link($roundDir) || is_link($userDir) ? null : "$userDir/$name.$extension"; // 任何一级替换为链接都不能访问站点数据目录外的文件。
    }

    /** 给源码页读取一份交卷。读者失去场次资格或对方作品未开放时，在打开文件前返回 null。 */
    public static function forReader(int $id, string $root): ?array
    {
        $entry = Database::rows("SELECT p.filename, p.probname, u.nickname, e.* FROM compscore e
            JOIN problem p ON p.pid=e.pid JOIN userinfo u ON u.uid=e.uid WHERE e.csid=?", [$id])[0] ?? null;
        if (!$entry) return null;
        $round = ContestRound::forReader((int)$entry['ctid']); // 选手已被移出分组时，不再允许读取该场交卷。
        if (!$round || !isset($round['problems'][$entry['pid']]) ||
            ((int)$entry['uid'] !== (int)$_SESSION['ID'] && !$round['otherEntriesVisible'])) return null;

        $entry['round'] = $round; // 源码页使用同一权限快照展示成绩。
        $entry['code'] = ''; // 答案压缩包和遗失源码保留交卷资料，源码区留空。
        $path = self::sourcePath($root, $entry['ctid'], $entry['uid'], $entry['filename'], $entry['lang']);
        if ((int)$entry['lang'] !== 3 && $path && is_file($path) && !is_link($path)) {
            $source = file_get_contents($path); // 只有通过场次资格和本地路径检查才打开文件。
            if ($source !== false) $entry['code'] = mb_check_encoding($source, 'UTF-8') ? $source : mb_convert_encoding($source, 'UTF-8', 'GBK'); // 早期选手用 GBK 保存的文件需转换后展示。
        }
        return $entry;
    }

    /** 读取本次要评测的确切源码字节。复核时重新检查路径，重交后的同名文件可能已经换掉。 */
    private static function sourceForGrading(array $entry, string $root): string
    {
        clearstatcache(); // 同一请求会在评测前后两次检查磁盘，不能沿用旧文件状态。
        $path = self::sourcePath($root, $entry['ctid'], $entry['uid'], $entry['filename'], $entry['lang']);
        if (!$path || !is_file($path) || is_link($path)) throw new RuntimeException('比赛源码不可读。');
        $code = file_get_contents($path, false, null, 0, GradingLimits::SOURCE_BYTES + 1); // 多读一个字节即可识别超限文件。
        if ($code === false || strlen($code) > GradingLimits::SOURCE_BYTES) throw new RuntimeException('比赛源码不可读或过大。');
        return $code;
    }

    /** 评测机工作时允许继续交卷，只在回写成绩前锁定本交卷，复核仍是已评测的那份文件。 */
    private static function saveGradedSource(int $id, array $entry, string $code, string $root, Judging $job): void
    {
        $db = Database::connection();
        $db->beginTransaction(); // 此锁只覆盖本地复核和写分，不等待评测机。
        try {
            $current = Database::rows('SELECT lang FROM compscore WHERE csid=? FOR UPDATE', [$id])[0] ?? null; // 重交也先更新这一行，再替换磁盘文件。
            if (!$current || (int)$current['lang'] !== (int)$entry['lang'] || self::sourceForGrading($entry, $root) !== $code) {
                throw new UnexpectedValueException('评测期间选手已重新交卷，请重新评测。'); // 新交卷保留自己的待评状态。
            }
            $job->saveContest($id); // 写入时仍持有交卷行锁。
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack(); // 任何检查或写库异常都释放交卷锁。
            throw $error;
        }
    }

    /**
     * 在指定机位重判一份比赛交卷。只在编译失败或全部测试点完成时保存分数。
     * @return ?array 供旧版 judge.php 使用的得分、判定串、耗时、内存。编译失败只有前两项，机位忙返回 null。
     */
    public static function grade(int $id, int $judgeId, string $root): ?array
    {
        $entry = Database::rows("SELECT e.lang, e.uid, e.ctid, p.pid, p.filename, p.datacnt, p.timelimit, p.memorylimit, p.plugin
            FROM compscore e JOIN problem p ON p.pid=e.pid WHERE e.csid=?", [$id])[0] ?? null; // 语言和题目限制都采用已登记的值。
        if (!$entry) throw new OutOfBoundsException('交卷不存在。');
        $code = self::sourceForGrading($entry, $root); // 保留实际送评的字节，即使选手在评测期间又上传同名文件。
        $roundDirectory = rtrim($root, '/').'/'.$entry['ctid']; // 评测机使用场次目录下的参赛人文件布局。
        $job = new Judging($entry, (int)$entry['uid'], (int)$entry['lang'], $roundDirectory);
        if (!$job->acquire($judgeId)) return null; // 当前机位未接受任务，保留已经公布的成绩。
        try {
            if ($job->compile($code)) { // 整次重判都对应这份已读取的源码。
                for ($point = 1; $point <= $entry['datacnt']; $point++) $job->run($point); // 测试点按题目规定顺序累计。
                $result = [$job->score(), $job->verdicts(), $job->totalTime(), $job->averageMemory()]; // 与批量评测页约定的字段顺序。
            } else {
                $result = [0, 'C']; // 编译未通过，无运行耗时和内存。
            }
            self::saveGradedSource($id, $entry, $code, $root, $job); // 仅完整且仍对应当前源码的评测可更新成绩。
            return $result;
        } finally {
            $job->unlock(); // 保存异常或评测失败也要归还机位。
        }
    }

    /** 仅供有「查看比赛」权限的导出入口使用；同一选手改用别的语言后，只打包当前记录指向的文件。 */
    public static function sourceFiles(int $round, string $root): array
    {
        $entries = Database::rows("SELECT e.uid, e.lang, p.filename FROM compscore e
            JOIN problem p ON p.pid=e.pid JOIN userinfo u ON u.uid=e.uid WHERE e.ctid=? ORDER BY e.uid", [$round]);
        $files = []; // 压缩包键是相对路径，值是已验证的实际文件。
        foreach ($entries as $entry) {
            $path = self::sourcePath($root, $round, $entry['uid'], $entry['filename'], $entry['lang']); // 跟随交卷记录的当前语言，略过历史重交留下的文件。
            if ($path && is_file($path) && !is_link($path)) $files[$entry['uid'].'/'.basename($path)] = $path;
        }
        return $files;
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

    /** 读取单个选手在指定场次、题目的成绩及其本人资料。 */
    public static function entrantResult($userId, $problemId, $roundId)
    {
        return Database::rows(
            "SELECT *
             FROM compscore, userinfo
             WHERE compscore.uid=? AND userinfo.uid=compscore.uid AND compscore.pid=? AND compscore.ctid=?",
            [$userId, $problemId, $roundId]
        );
    }

    /** 按选手、题目和场次找到已有的交卷编号。 */
    private static function lookup($userId, $problemId, $roundId)
    {
        return Database::rows(
            "SELECT csid FROM compscore WHERE uid=? AND pid=? AND ctid=?",
            [$userId, $problemId, $roundId]
        );
    }

    /** 再次交卷时清掉旧分和资源用量，将这条记录恢复到待评状态。 */
    private static function replaceSource($time, $language, $roundId, $userId, $problemId)
    {
        return Database::execute(
            "UPDATE compscore SET score=0, result=NULL, memory=0, runtime=0, subtime=?, lang=? WHERE ctid=? AND uid=? AND pid=?",
            [$time, $language, $roundId, $userId, $problemId]
        );
    }

    /** 第一次交本题时为该场选手登记一条待评记录。 */
    private static function create($roundId, $userId, $problemId, $time, $language)
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
        $ids = array_values(array_unique(array_filter($ids, static fn($id) => is_scalar($id) && ctype_digit((string)$id) && (int)$id > 0))); // 每个选中的交卷只评一次。
        if (!$ids) return []; // 管理员未勾选交卷时清单为空。
        $marks = implode(',', array_fill(0, count($ids), '?')); // 每个管理员所选编号各自绑定，不作为 SQL 片段。
        return Database::rows("SELECT e.*, u.nickname, u.realname, p.probname, p.filename FROM compscore e
            JOIN userinfo u ON u.uid=e.uid JOIN problem p ON p.pid=e.pid WHERE e.csid IN ($marks)
            ORDER BY e.csid", $ids);
    }

    /**
     * 保存上传并返回交卷编号。先锁定场次和题单，再读取参赛资格，
     * 避免参赛者等锁期间错过截止时间或管理员将该题移出题单。
     * @return ?int 场次没有向选手开放时返回 null，其他交卷返回 csid。
     * @throws DomainException 上传文件或语言不合法。
     */
    public static function submitSource(int $round, int $problem, int $user, string $extension, array $upload, string $root): ?int
    {
        $db = Database::connection(); // 查找旧交卷与登记新成绩必须使用同一事务。
        $backup = null; // 只有替换了上一份同路径源码时才需要还原它。
        $installed = false; // 记录新文件是否已落盘，决定异常时该删除哪一份。
        $db->beginTransaction(); // 同场并发交卷统一先取得场次锁。
        try {
            Database::statement("SELECT ct.ctid FROM comptime ct JOIN compbase cb ON cb.cbid=ct.cbid
                WHERE ct.ctid=? FOR UPDATE", [$round]); // 同时锁定题单，防止校验完题号后管理员移除它。
            $rules = ContestRound::forReader($round); // 取得锁以后重新检查截止时间和对该选手开放的题目。
            if (!$rules || !$rules['acceptsSubmissions'] || !isset($rules['problems'][$problem])) {
                $db->rollBack(); // 不接受这次交卷，立即释放场次锁。
                return null;
            }
            $language = Language::id($extension); // 只接受评测机登记的源语言和答案包。
            $filename = Problem::byId($problem)[0]['filename'] ?? ''; // 比赛源码必须沿用题目的标准文件名。
            $path = $language === null ? null : self::sourcePath($root, $round, $user, $filename, $language);
            $temporary = $upload['tmp_name'] ?? ''; // 只有本次请求真实上传的临时文件可以入库。
            if (!$path || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
                !is_uploaded_file($temporary) || $upload['size'] < 1 || $upload['size'] > GradingLimits::SOURCE_BYTES) {
                throw new DomainException('源文件或提交语言无效。');
            }
            $directory = dirname($path); // 首交需要创建该场次下的选手目录。
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('不能创建交卷目录。');
            }
            $entry = self::lookup($user, $problem, $round)[0] ?? null; // 一个选手在一场比赛每题保留一个交卷编号。
            if ($entry) {
                $id = $entry['csid']; // 重交沿用此前的成绩单和源码链接。
                self::replaceSource(time(), $language, $round, $user, $problem); // 旧成绩立即回到待评，待文件和记录一起提交。
            } else {
                self::create($round, $user, $problem, time(), $language);
                $id = (int)$db->lastInsertId(); // 首次提交的交卷编号用于跳转源码页。
            }
            // 同路径旧源码先改名保留，等成绩记录提交成功才可删除。
            if (is_file($path)) {
                $backup = $path.'.'.bin2hex(random_bytes(8)); // 备份名不与其他请求或既有源码重名。
                if (!rename($path, $backup)) throw new RuntimeException('不能保存上次提交。');
            }
            if (!move_uploaded_file($temporary, $path)) throw new RuntimeException('交卷文件写入失败。');
            // 从这里起，事务失败还需删除刚安装的新文件。
            $installed = true;
            $db->commit(); // 成功后新文件与待评记录才同时生效。
        } catch (Throwable $error) {
            // 文件系统不随数据库回滚；下面按安装进度恢复旧文件。
            if ($db->inTransaction()) $db->rollBack();
            if ($installed) unlink($path); // 先移除未能登记的新源码。
            if ($backup && is_file($backup)) rename($backup, $path); // 用原交卷恢复选手提交前的状态。
            throw $error;
        }
        if ($backup) unlink($backup); // 成绩记录已提交，旧源码备份可以删除。
        return $id;
    }

    /** 榜单总分降序，同分使用相同名次并跳过相应席位（1、1、3）。未开放他人成绩时仍保持用户编号顺序。 */
    public static function rankedResults(array $round): array
    {
        $users = self::roundResults($round['ctid'], array_keys($round['problems'])); // 只用该读者可见题目的得分。
        if (!$round['otherEntriesVisible']) return $users; // 名次和行顺序都不能提前透露成绩。
        uasort($users, static fn($a, $b) => $b['total'] <=> $a['total'] ?: $a['user']['uid'] <=> $b['user']['uid']);
        $position = 0; // 计入每位选手，同分也占一席。
        $previous = null; // 首位选手即使零分也必须有第一名。
        foreach ($users as &$entrant) {
            $position++;
            if ($previous !== $entrant['total']) $rank = $position; // 只有分数改变才更新显示名次。
            $entrant['rank'] = $rank;
            $previous = $entrant['total']; // 下一位使用相同总分时沿用这个名次。
        }
        return $users;
    }

    /**
     * 一次读完整场交卷以生成成绩表。每名选手的 marks 以题号为键，未交题的键不存在。
     * @return array 用户编号对应 user、marks 和 total，total 为已交各题得分之和。
     */
    public static function roundResults($id, ?array $visibleProblems = null): array
    {
        $query = "SELECT e.*, u.nickname, u.realname, u.email, p.probname FROM compscore e
            JOIN userinfo u ON u.uid=e.uid JOIN problem p ON p.pid=e.pid WHERE e.ctid=?";
        $values = [$id]; // 所有榜单都从本场交卷开始筛选。
        // 管理页取全部交卷。公开成绩表只统计读者能看的题，避免由总分推算隐藏题成绩。
        if ($visibleProblems !== null) {
            if (!$visibleProblems) return [];
            $marks = implode(',', array_fill(0, count($visibleProblems), '?')); // 为该读者可见的每题保留单独的绑定位置。
            $query .= " AND e.pid IN ($marks)";
            array_push($values, ...$visibleProblems); // 顺序与 SQL 中的可见题号占位符对应。
        }
        $entries = Database::rows($query.' ORDER BY e.uid, e.pid', $values); // 未发布排行时，列表沿用用户编号顺序。
        $users = [];
        foreach ($entries as $entry) {
            $uid = $entry['uid']; // 同一选手的不同题目归到同一行。
            if (!isset($users[$uid])) $users[$uid] = ['user'=>$entry, 'marks'=>[], 'total'=>0];
            // 保留每题原始记录，让成绩单能同时显示单题结果和总分。
            $users[$uid]['marks'][$entry['pid']] = $entry;
            $users[$uid]['total'] += $entry['score']; // 上方筛过不可见题目，此处总分不会包含隐藏题。
        }
        return $users;
    }

}
