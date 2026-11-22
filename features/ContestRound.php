<?php
// 比赛场次、开放时间和分组。例如 ContestRound::withGroup($roundId)。
final class ContestRound
{
    public static function withGroup($roundId)
    {
        return Database::rows(
            "SELECT comptime.*, compbase.cname, groups.*
             FROM comptime, compbase, groups
             WHERE comptime.cbid=compbase.cbid AND comptime.ctid=? AND groups.gid=comptime.group",
            [$roundId]
        );
    }

    public static function create($contestId, $intro, $start, $end, $showScores, $groupId)
    {
        return Database::execute(
            "INSERT INTO comptime(cbid, intro, starttime, endtime, showscore, `group`) VALUES(?, ?, ?, ?, ?, ?)",
            [$contestId, $intro, $start, $end, $showScores, $groupId]
        );
    }

    public static function edit($contestId, $intro, $start, $end, $showScores, $groupId, $id)
    {
        return Database::execute(
            "UPDATE comptime SET cbid=?, intro=?, starttime=?, endtime=?, showscore=?, `group`=? WHERE ctid=?",
            [$contestId, $intro, $start, $end, $showScores, $groupId, $id]
        );
    }

    public static function withTitle($id)
    {
        return Database::rows(
            "SELECT comptime.*, compbase.cname
             FROM comptime, compbase
             WHERE comptime.cbid=compbase.cbid AND comptime.ctid=?",
            [$id]
        );
    }

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

    public static function overview($roundId)
    {
        return Database::rows(
            "SELECT compbase.cname, compbase.contains, comptime.starttime, comptime.endtime, comptime.showscore, comptime.intro, groups.*
             FROM comptime, compbase, groups
             WHERE comptime.cbid=compbase.cbid AND comptime.ctid=? AND comptime.group=groups.gid",
            [$roundId]
        );
    }

    public static function rules($roundId)
    {
        return Database::rows(
            "SELECT cname, starttime, endtime, contains, intro, comptime.readforce, groups.*
             FROM compbase, comptime, groups
             WHERE comptime.cbid=compbase.cbid AND comptime.ctid=? AND comptime.group=groups.gid",
            [$roundId]
        );
    }

    public static function scoreboardRules($roundId)
    {
        return Database::rows(
            "SELECT comptime.starttime, comptime.endtime, compbase.contains, comptime.showscore
             FROM compbase, comptime
             WHERE comptime.cbid=compbase.cbid AND comptime.ctid=?",
            [$roundId]
        );
    }

    public static function directory()
    {
        return Database::rows(
            "SELECT comptime.cbid, comptime.ctid, compbase.cname, comptime.intro
             FROM comptime, compbase
             WHERE comptime.cbid=compbase.cbid
             ORDER BY comptime.ctid DESC"
        );
    }

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

    public static function byContest(): array
    {
        $byContest = [];
        foreach (Database::rows("SELECT cbid, ctid, starttime FROM comptime ORDER BY starttime") as $round) {
            $byContest[$round['cbid']][] = $round;
        }
        return $byContest;
    }

    public static function counts($time): array
    {
        return Database::rows("SELECT COUNT(CASE WHEN starttime<? AND endtime>? THEN 1 END) AS active,
            COUNT(CASE WHEN starttime>? AND endtime>? THEN 1 END) AS upcoming FROM comptime",
            [$time, $time, $time, $time])[0];
    }

}
