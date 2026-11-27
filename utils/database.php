<?php
/**
 * 业务查询在 features/ 定义。这里建立请求内共享的 PDO 连接，用原生预处理把每个值绑定到对应问号。
 * 例：$row = Database::rows('SELECT uid FROM userinfo WHERE usr=?', ['student'])[0] ?? null;
 */
final class Database
{
    private static $connection;

    /** 同一个请求共享一个连接，业务主体可在此连接上开启事务。错误作为 PDOException 抛出。 */
    public static function connection()
    {
        global $cfg;
        if (!self::$connection) { // 事务中的读写以及行锁必须落在同一条连接。
            $dsn = 'mysql:host='.$cfg['data_server'].';dbname='.$cfg['data_database'].';charset=utf8';
            self::$connection = new PDO($dsn, $cfg['data_uid'], $cfg['data_pwd'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // 让调用方能够回滚未完成的操作。
                PDO::ATTR_EMULATE_PREPARES => false, // SQL 文本和值分别交给数据库。
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 查询行用业务字段名称访问。
            ]);
        }
        return self::$connection;
    }

    /** 用问号顺序绑定 $values。返回已执行的 PDOStatement，适用于需要锁行或读取结果的业务查询。 */
    public static function statement(string $sql, array $values = []): PDOStatement
    {
        global $Query_Times;
        $Query_Times = ($Query_Times ?? 0) + 1; // 页脚统计实际查询次数。
        $statement = self::connection()->prepare($sql); // 先提交查询结构，再绑定值。
        // PDO 的问号参数从 1 开始。LIMIT、OFFSET 等整数保持整数类型。
        foreach (array_values($values) as $offset => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            if ($value === null) $type = PDO::PARAM_NULL; // SQL NULL 与空字符串含义不同。
            $statement->bindValue($offset + 1, $value, $type);
        }
        $statement->execute(); // 所有值就位后再执行，失败会抛出 PDOException。
        return $statement;
    }

    /** 按列名返回所有结果行，零行返回空数组。 */
    public static function rows(string $sql, array $values = []): array
    {
        return self::statement($sql, $values)->fetchAll();
    }

    /** 返回受影响行数；写入失败会抛异常，写入零行不等同于数据库错误。 */
    public static function execute(string $sql, array $values = []): int
    {
        return self::statement($sql, $values)->rowCount();
    }
}
