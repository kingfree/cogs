<?php
require_once __DIR__."/sql-query.php";
// 各页面共享数据库连接，每个查询对象保留自己的结果集。
class DataAccess
{
    private static $connection;
    private $rows = array();

    public static function connection()
    {
        global $cfg;
        if (!self::$connection) {
            $dsn = 'mysql:host='.$cfg['data_server'].';dbname='.$cfg['data_database'].';charset=utf8';
            self::$connection = new PDO($dsn, $cfg['data_uid'], $cfg['data_pwd'], array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ));
        }
        return self::$connection;
    }

    public static function statement($sql, array $params = [])
    {
        global $Query_Times;
        $Query_Times++;
        if ($sql instanceof SqlQuery) {
            $params = $sql->values;
            $sql = $sql->text;
        }
        $statement = self::connection()->prepare($sql);
        foreach (array_values($params) as $offset => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            if ($value === null) $type = PDO::PARAM_NULL;
            $statement->bindValue($offset + 1, $value, $type);
        }
        $statement->execute();
        return $statement;
    }

    public function dosql($sql, $params = [])
    {
        $statement = self::statement($sql, $params);
        $this->rows = $statement->columnCount() ? $statement->fetchAll(PDO::FETCH_BOTH) : array();
        return count($this->rows);
    }

    public function rtnrlt($rownum = 0)
    {
        return isset($this->rows[$rownum]) ? $this->rows[$rownum] : false;
    }

    public function Query($sql)
    {
        return $this->dosql($sql);
    }
}
