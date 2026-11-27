<?php
/**
 * 比赛场次把一张题单安排在指定时间，记录参赛分组及是否公开成绩。
 * contest/submit.php 交卷前读取场次：$round = ContestRound::forReader(1);
 * ctid 指一次开赛，cbid 指可重复使用的题单。给比赛排第二场时应保留原 cbid。
 */
final class ContestRound
{
    /** 开赛与截止的当秒均可交卷；列表、题面和交卷判断使用同一时间边界。 */
    public static function phase(array $round, int $now): string
    {
        if ($now < $round['starttime']) return 'upcoming'; // 开始前仍可在赛程表预告。
        return $now > $round['endtime'] ? 'finished' : 'active'; // 过截止秒才结束。
    }

    /** 管理员按场次编号查看开赛与截止时间、参赛组和题单标题。 */
    public static function withGroup($roundId)
    {
        return Database::rows(
            "SELECT ct.*, ct.`group` AS gid, COALESCE(g.gname, IF(ct.`group`=0, '所有用户', '已删除分组')) AS gname, cb.cname FROM comptime ct
             JOIN compbase cb ON cb.cbid=ct.cbid LEFT JOIN groups g ON g.gid=ct.`group` WHERE ct.ctid=?",
            [$roundId]
        );
    }

    /** 将排期表单的年月日时分秒按站点时区转成 Unix 秒。拒绝 2 月 31 日等会被 mktime 自动滚动的值。 */
    private static function scheduledTime(array $form, string $prefix): int
    {
        $parts = []; // 组装开赛或截止的六个日期字段，顺序与下方解包相同。
        foreach (['y', 'm', 'd', 'h', 'i', 's'] as $part) {
            $value = $form[$prefix.'_'.$part] ?? ''; // st 和 et 使用同一套年月日时分秒校验。
            if (!is_scalar($value) || !ctype_digit((string)$value)) throw new DomainException('请输入完整的比赛日期和时间。');
            $parts[] = (int)$value; // 允许 09 这样的月份写法。
        }
        [$year, $month, $day, $hour, $minute, $second] = $parts;
        if (!checkdate($month, $day, $year) || $hour > 23 || $minute > 59 || $second > 59) { // 禁止 PHP 将越界日期自动变成下月的新日期。
            throw new DomainException('比赛日期或时间无效。');
        }
        $timestamp = mktime($hour, $minute, $second, $month, $day, $year); // 以站点本地时区保存，与编辑页面回显一致。
        if ($year < 1970 || $timestamp === false || $timestamp < 0 || $timestamp > 4294967295) { // 旧库场次时间列是无符号 32 位秒数。
            throw new DomainException('比赛日期超出可保存范围。');
        }
        return $timestamp;
    }

    /** 新建或更改一次排期。ctid 标识场次，表单中的 cbid 标识要使用的题单。 */
    public static function save(?int $id, array $form): void
    {
        $start = self::scheduledTime($form, 'st'); // 日期中的每项都来自排期表单。
        $end = self::scheduledTime($form, 'et'); // 开始与结束共享站点时区。
        if ($end <= $start) throw new DomainException('截止时间必须晚于开赛时间。');
        $contest = filter_var($form['cbid'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $group = filter_var($form['group'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]); // 0 是公开场次，无需在分组表登记。
        if ($contest === false || !Contest::byId($contest)) throw new DomainException('比赛题单不存在。');
        if ($group === false || ($group > 0 && !UserGroup::byId($group))) throw new DomainException('参赛分组不存在。');
        $values = [$contest, $form['intro'] ?? '', $start, $end, (int)!empty($form['showscore']), $group]; // 未勾选公布成绩的场次保存为 0。
        if ($id === null) {
            Database::execute('INSERT INTO comptime(cbid, intro, starttime, endtime, showscore, `group`)
                VALUES(?, ?, ?, ?, ?, ?)', $values); // 为主办方添加一条独立开赛记录。
        } else {
            if (!self::withTitle($id)) throw new DomainException('比赛场次不存在。');
            $values[] = $id; // 只更新正在编辑的场次，同一题单的其他排期不受影响。
            Database::execute('UPDATE comptime SET cbid=?, intro=?, starttime=?, endtime=?, showscore=?, `group`=? WHERE ctid=?', $values);
        }
    }

    /** 读取编辑表单需要的场次原值和题单名。 */
    public static function withTitle($id)
    {
        return Database::rows(
            "SELECT comptime.*, compbase.cname
             FROM comptime, compbase
             WHERE comptime.cbid=compbase.cbid AND comptime.ctid=?",
            [$id]
        );
    }

    /** 按读者阅读等级列出场次；进入题目或交卷时还须核对分组和时间。 */
    public static function visible($readLevel)
    {
        return Database::rows(
            "SELECT ct.*, ct.`group` AS gid, COALESCE(g.gname, IF(ct.`group`=0, '所有用户', '已删除分组')) AS gname, cb.cname, cb.ouid, u.realname, u.nickname
             FROM comptime ct JOIN compbase cb ON cb.cbid=ct.cbid JOIN userinfo u ON u.uid=cb.ouid
             LEFT JOIN groups g ON g.gid=ct.`group` WHERE ct.readforce<=? ORDER BY ct.starttime DESC",
            [$readLevel]
        );
    }

    /**
     * 题面、交卷、源码和成绩表共用场次权限。返回 null 表示场次不存在或尚未向当前读者开放。
     * 比赛允许参赛者做题库未公开的题，故只按场次分组、两级阅读门槛和题单判断。
     * problems 是按原赛题顺序排列的 pid => 题名。具有「查看比赛」权限者可在开赛前核对题面。
     */
    public static function forReader(int $id): ?array
    {
        $round = Database::rows("SELECT ct.*, ct.`group` AS gid, COALESCE(g.gname, IF(ct.`group`=0, '所有用户', '已删除分组')) AS gname, cb.cname, cb.contains FROM comptime ct
            JOIN compbase cb ON cb.cbid=ct.cbid LEFT JOIN groups g ON g.gid=ct.`group` WHERE ct.ctid=?", [$id])[0] ?? null;
        if (!$round) return null; // 已撤销的场次与不可见场次对读者使用相同响应。
        $review = Privilege::allows('查看比赛'); // 裁判可提前核对未开放的场次。
        $now = time(); // 同一次读取的开赛判断和交卷判断共用同一时刻。
        $round['phase'] = self::phase($round, $now); // 开始和截止的当秒都按赛中处理。
        if (!$review && ($round['phase'] === 'upcoming' || $round['readforce'] > $_SESSION['readforce'] ||
            !UserGroup::includes($round['gid'], $_SESSION['group']))) return null;

        $ids = Contest::problemIds($round['contains']); // 沿用题单的排列，而非题目创建顺序。
        $round['problems'] = []; // 没有可见赛题的场次仍可显示介绍。
        if ($ids) {
            $marks = implode(',', array_fill(0, count($ids), '?')); // 每个题号独立绑定，题单内容不会进入 SQL 文本。
            $query = "SELECT pid, probname FROM problem WHERE pid IN ($marks)";
            $values = $ids; // 仅有普通读者需要在题号后再绑定阅读等级。
            if (!$review) {
                $query .= ' AND readforce<=?'; // 裁判预览之外，隐藏高于当前阅读等级的题目。
                $values[] = (int)$_SESSION['readforce'];
            }
            $titles = array_column(Database::rows($query, $values), 'probname', 'pid'); // 一次查齐题名，再按题单顺序重排。
            foreach ($ids as $pid) if (isset($titles[$pid])) $round['problems'][$pid] = $titles[$pid];
        }

        // 未公布成绩时本人仍可核对已交的源码；其他选手的源码在赛后随成绩开放。
        $round['acceptsSubmissions'] = $review || ($round['phase'] === 'active' && !empty($_SESSION['ID']));
        $round['scoresVisible'] = $review || (bool)$round['showscore']; // 赛中可按设置向本人显示自己的分数。
        $round['otherEntriesVisible'] = $review || ($round['scoresVisible'] && $round['phase'] === 'finished'); // 只有赛后公布成绩才向其他选手公开源码。
        return $round;
    }

    /** 站点地图按场次列出题单名称和介绍。 */
    public static function directory()
    {
        return Database::rows(
            "SELECT comptime.cbid, comptime.ctid, compbase.cname, comptime.intro
             FROM comptime, compbase
             WHERE comptime.cbid=compbase.cbid
             ORDER BY comptime.ctid DESC"
        );
    }

    /** 列出与给定时间区间有交集的比赛，包含已开始且仍在进行的场次。 */
    public static function upcomingBetween($start, $end)
    {
        return Database::rows(
            "SELECT ct.*, ct.`group` AS gid, COALESCE(g.gname, IF(ct.`group`=0, '所有用户', '已删除分组')) AS gname, cb.cname, cb.ouid, u.nickname
             FROM comptime ct JOIN compbase cb ON cb.cbid=ct.cbid JOIN userinfo u ON u.uid=cb.ouid
             LEFT JOIN groups g ON g.gid=ct.`group` WHERE ct.endtime > ? AND ct.starttime < ? ORDER BY ct.starttime ASC",
            [$start, $end]
        );
    }

    /** 按交卷记录列出该选手参加过的场次，一场有多个题目时可能返回多行。 */
    public static function forUser($userId)
    {
        return Database::rows(
            "SELECT compbase.cbid, compbase.cname, compscore.subtime, comptime.ctid
             FROM compscore, comptime, compbase
             WHERE compscore.uid=? AND comptime.ctid=compscore.ctid AND comptime.cbid=compbase.cbid
             ORDER BY comptime.endtime DESC",
            [$userId]
        );
    }

    /** 一次读取题单下的所有场次，结果以 cbid 为键，各场按开始时间排列。 */
    public static function byContest(): array
    {
        $byContest = []; // 没有任何排期时，管理页仍可列出未开赛的题单。
        foreach (Database::rows("SELECT cbid, ctid, starttime FROM comptime ORDER BY starttime") as $round) { // 相同题单下的开赛记录按时间排列。
            $byContest[$round['cbid']][] = $round;
        }
        return $byContest;
    }

    /** 统计当前进行和即将开始的场次，导航栏读取 active 和 upcoming 两项。 */
    public static function counts($time): array
    {
        return Database::rows("SELECT COUNT(CASE WHEN starttime<? AND endtime>? THEN 1 END) AS active,
            COUNT(CASE WHEN starttime>? AND endtime>? THEN 1 END) AS upcoming FROM comptime",
            [$time, $time, $time, $time])[0];
    }

}
