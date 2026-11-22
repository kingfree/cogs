<?php
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

    // 旧页面自行添加引号；新查询通过 dosql 的第二个参数绑定。
    public static function escape($value)
    {
        return substr(self::connection()->quote($value), 1, -1);
    }

    public function dosql($sql, $params = array())
    {
        global $Query_Times;
        $Query_Times++;
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);
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
