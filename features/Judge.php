<?php
// 评测机地址、优先级和启用状态。例如 Judge::enabled()。
final class Judge
{
    public static function enabled()
    {
        return Database::rows(
            "SELECT grid, address, memo FROM grader WHERE enabled=1 ORDER BY priority DESC"
        );
    }

    public static function enabledById($id)
    {
        return Database::rows(
            "SELECT grid, address, memo FROM grader WHERE enabled=1 AND grid=?",
            [$id]
        );
    }

    public static function create($address, $priority, $enabled, $memo)
    {
        return Database::execute(
            "INSERT INTO grader(address, priority, enabled, memo) VALUES(?, ?, ?, ?)",
            [$address, $priority, $enabled, $memo]
        );
    }

    public static function edit($address, $priority, $enabled, $memo, $id)
    {
        return Database::execute(
            "UPDATE grader SET address=?, priority=?, enabled=?, memo=? WHERE grid=?",
            [$address, $priority, $enabled, $memo, $id]
        );
    }

    public static function address($id)
    {
        return Database::rows(
            "SELECT address FROM grader WHERE grid=?",
            [$id]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM grader WHERE grid=?",
            [$id]
        );
    }

    public static function all()
    {
        return Database::rows(
            "SELECT * FROM grader"
        );
    }
}
