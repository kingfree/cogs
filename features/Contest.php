<?php
/**
 * 比赛题单保存名称、出题人及题目的顺序，一张题单可以安排多场比赛。
 * contest/editcompbase.php 编辑题单，比赛起止时间由 ContestRound 单独保存。
 * 在已加载站点配置的入口读取题单：$contest = Contest::byId(1)[0] ?? null;
 */
final class Contest
{
    /** 列出题单及其组织者，供比赛管理页一次读取。 */
    public static function withOrganizers()
    {
        return Database::rows(
            "SELECT compbase.*, userinfo.nickname, userinfo.realname, userinfo.email
             FROM compbase, userinfo
             WHERE userinfo.uid=compbase.ouid
             ORDER BY compbase.cbid DESC"
        );
    }

    /** 按表单顺序保存题单；$problemIds 是用冒号分隔的题号，例如 12:7:23。 */
    public static function create($name, $problemIds, $owner)
    {
        return Database::execute(
            "INSERT INTO compbase(cname, contains, ouid) VALUES(?, ?, ?)",
            [$name, $problemIds, $owner]
        );
    }

    /** 替换现有题单的名称和有序题号；所有引用该 cbid 的场次都会读到改动。 */
    public static function edit($name, $problemIds, $id)
    {
        return Database::execute(
            "UPDATE compbase SET cname=?, contains=? WHERE cbid=?",
            [$name, $problemIds, $id]
        );
    }

    /** 按题单编号 cbid 读取，找不到时为空数组。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM compbase WHERE cbid=?",
            [$id]
        );
    }

    /** 返回安排场次所需的题单编号和名称。 */
    public static function choices()
    {
        return Database::rows(
            "SELECT cname, cbid FROM compbase"
        );
    }
}
