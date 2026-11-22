<?php
// 比赛题单和组织者。例如 Contest::withOrganizers()。
final class Contest
{
    public static function withOrganizers()
    {
        return Database::rows(
            "SELECT compbase.*, userinfo.nickname, userinfo.realname, userinfo.email
             FROM compbase, userinfo
             WHERE userinfo.uid=compbase.ouid
             ORDER BY compbase.cbid DESC"
        );
    }

    public static function create($name, $problemIds, $owner)
    {
        return Database::execute(
            "INSERT INTO compbase(cname, contains, ouid) VALUES(?, ?, ?)",
            [$name, $problemIds, $owner]
        );
    }

    public static function edit($name, $problemIds, $id)
    {
        return Database::execute(
            "UPDATE compbase SET cname=?, contains=? WHERE cbid=?",
            [$name, $problemIds, $id]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM compbase WHERE cbid=?",
            [$id]
        );
    }

    public static function choices()
    {
        return Database::rows(
            "SELECT cname, cbid FROM compbase"
        );
    }
}
