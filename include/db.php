<?php
// PDO 连接与绑定。示例 Database::rows('SELECT uid FROM userinfo WHERE usr = ?', [$name])。
final class Database
{
    private static $connection;

    public static function connection()
    {
        global $cfg;
        if (!self::$connection) {
            $dsn = 'mysql:host='.$cfg['data_server'].';dbname='.$cfg['data_database'].';charset=utf8';
            self::$connection = new PDO($dsn, $cfg['data_uid'], $cfg['data_pwd'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$connection;
    }

    public static function statement(string $sql, array $values = []): PDOStatement
    {
        global $Query_Times;
        $Query_Times = ($Query_Times ?? 0) + 1;
        $statement = self::connection()->prepare($sql);
        foreach (array_values($values) as $offset => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            if ($value === null) $type = PDO::PARAM_NULL;
            $statement->bindValue($offset + 1, $value, $type);
        }
        $statement->execute();
        return $statement;
    }

    public static function rows(string $sql, array $values = []): array
    {
        return self::statement($sql, $values)->fetchAll();
    }

    public static function execute(string $sql, array $values = []): int
    {
        return self::statement($sql, $values)->rowCount();
    }
}
