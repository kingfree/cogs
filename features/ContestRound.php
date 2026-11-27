<?php
/**
 * 比赛场次把一张题单安排在指定时间，记录参赛分组及是否公开成绩。
 * contest/submit.php 交卷前读取场次：$round = ContestRound::forReader(1);
 * ctid 指一次开赛，cbid 指可重复使用的题单。给比赛排第二场时应保留原 cbid。
 */
final class ContestRound
{
    /** 管理员按场次编号查看开赛与截止时间、参赛组和题单标题。 */
    public static function withGroup($roundId)
    {
        return Database::rows(
            "SELECT comptime.*, compbase.cname, groups.*
             FROM comptime, compbase, groups
             WHERE comptime.cbid=compbase.cbid AND comptime.ctid=? AND groups.gid=comptime.group",
            [$roundId]
        );
    }

    /** 给既有题单安排一次开赛，$start 和 $end 均为 Unix 秒。 */
    public static function create($contestId, $intro, $start, $end, $showScores, $groupId)
    {
        return Database::execute(
            "INSERT INTO comptime(cbid, intro, starttime, endtime, showscore, `group`) VALUES(?, ?, ?, ?, ?, ?)",
            [$contestId, $intro, $start, $end, $showScores, $groupId]
        );
    }

    /** 只改 $id 对应的场次，$showScores 控制成绩公布。 */
    public static function edit($contestId, $intro, $start, $end, $showScores, $groupId, $id)
    {
        return Database::execute(
            "UPDATE comptime SET cbid=?, intro=?, starttime=?, endtime=?, showscore=?, `group`=? WHERE ctid=?",
            [$contestId, $intro, $start, $end, $showScores, $groupId, $id]
        );
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
            "SELECT comptime.*, compbase.*, userinfo.realname, userinfo.nickname, groups.*
             FROM comptime, compbase, userinfo, groups
             WHERE comptime.readforce<=? AND comptime.cbid=compbase.cbid AND userinfo.uid=compbase.ouid AND comptime.group=groups.gid
             ORDER BY starttime DESC",
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
        $round = Database::rows("SELECT ct.*, ct.`group` AS gid, g.gname, cb.cname, cb.contains FROM comptime ct
            JOIN compbase cb ON cb.cbid=ct.cbid LEFT JOIN groups g ON g.gid=ct.`group` WHERE ct.ctid=?", [$id])[0] ?? null;
        if (!$round) return null;
        $review = Privilege::allows('查看比赛');
        $now = time();
        if (!$review && ($now < $round['starttime'] || $round['readforce'] > $_SESSION['readforce'] ||
            !UserGroup::includes($round['gid'], $_SESSION['group']))) return null;

        $ids = array_values(array_filter(explode(':', $round['contains']), 'ctype_digit'));
        $round['problems'] = [];
        if ($ids) {
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $query = "SELECT pid, probname FROM problem WHERE pid IN ($marks)";
            $values = $ids;
            if (!$review) {
                $query .= ' AND readforce<=?';
                $values[] = (int)$_SESSION['readforce'];
            }
            $titles = array_column(Database::rows($query, $values), 'probname', 'pid');
            foreach ($ids as $pid) if (isset($titles[$pid])) $round['problems'][$pid] = $titles[$pid];
        }

        // 未公布成绩时本人仍可核对已交的源码；其他选手的源码在赛后随成绩开放。
        $round['scoresVisible'] = $review || (bool)$round['showscore'];
        $round['otherEntriesVisible'] = $review || ($round['scoresVisible'] && $now > $round['endtime']);
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
            "SELECT comptime.*, compbase.*, userinfo.nickname, groups.*
             FROM comptime, compbase, userinfo, groups
             WHERE comptime.cbid=compbase.cbid AND userinfo.uid=compbase.ouid AND comptime.group=groups.gid AND endtime > ? AND starttime < ?
             ORDER BY starttime ASC",
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
        $byContest = [];
        foreach (Database::rows("SELECT cbid, ctid, starttime FROM comptime ORDER BY starttime") as $round) {
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
