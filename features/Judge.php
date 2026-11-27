<?php
/**
 * 评测机的站点配置，包括请求地址、是否启用和选机顺序。
 * submit/editgrader.php 修改配置；实际接单状态由远端评测机报告。
 * 获取自动选机候选：$candidates = Judge::enabled();
 */
final class Judge
{
    /** 自动选机时只取启用的机器，按配置的优先级从高到低试租。 */
    public static function enabled()
    {
        return Database::rows(
            "SELECT grid, address, memo FROM grader WHERE enabled=1 ORDER BY priority DESC"
        );
    }

    /** 手动选机也必须启用；管理员停用的机器即使传来旧编号也不会入选。 */
    public static function enabledById($id)
    {
        return Database::rows(
            "SELECT grid, address, memo FROM grader WHERE enabled=1 AND grid=?",
            [$id]
        );
    }

    /** 登记机器地址和优先级，$enabled 控制它是否参与选机。 */
    public static function create($address, $priority, $enabled, $memo)
    {
        return Database::execute(
            "INSERT INTO grader(address, priority, enabled, memo) VALUES(?, ?, ?, ?)",
            [$address, $priority, $enabled, $memo]
        );
    }

    /** 修改一个已登记的机位，变更在后续选机请求中生效。 */
    public static function edit($address, $priority, $enabled, $memo, $id)
    {
        return Database::execute(
            "UPDATE grader SET address=?, priority=?, enabled=?, memo=? WHERE grid=?",
            [$address, $priority, $enabled, $memo, $id]
        );
    }

    /** 管理员发送开、停机指令前按编号查地址。 */
    public static function address($id)
    {
        return Database::rows(
            "SELECT address FROM grader WHERE grid=?",
            [$id]
        );
    }

    /** 按编号读完整配置，供编辑表单回显。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM grader WHERE grid=?",
            [$id]
        );
    }

    /** 列出已登记的评测机，含停用项，供管理员维护。 */
    public static function all()
    {
        return Database::rows(
            "SELECT * FROM grader"
        );
    }
}
