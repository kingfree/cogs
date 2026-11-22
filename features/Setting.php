<?php
// 站点运行参数。例如 Setting::byId(1)。
final class Setting
{
    public static function change($value, $id)
    {
        return Database::execute(
            "UPDATE settings SET value=? WHERE ssid=?",
            [$value, $id]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM settings WHERE ssid=?",
            [$id]
        );
    }

    public static function byName($name)
    {
        return Database::rows(
            "SELECT * FROM settings WHERE name=?",
            [$name]
        );
    }

    public static function all()
    {
        return Database::rows(
            "SELECT * FROM settings"
        );
    }
}
