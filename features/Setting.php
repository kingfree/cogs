<?php
/**
 * 站点设置按 name 区分用途。bootstrap.php 在每个请求开始时加载为 $SET，管理页编辑时使用记录的 ssid。
 * 例：$rows = Setting::byName('style_pagesize');
 */
final class Setting
{
    /** 管理表单使用 ssid 更新值，保留设置名称。 */
    public static function change($value, $id)
    {
        return Database::execute(
            "UPDATE settings SET value=? WHERE ssid=?",
            [$value, $id]
        );
    }

    /** 编辑某项设置时读取对应行；不存在返回空列表。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM settings WHERE ssid=?",
            [$id]
        );
    }

    /** 业务按稳定的 name 查设置，调用者使用结果的 value。 */
    public static function byName($name)
    {
        return Database::rows(
            "SELECT * FROM settings WHERE name=?",
            [$name]
        );
    }

    /** 返回设置表各行，供请求启动时按 name 建立键值表。 */
    public static function all()
    {
        return Database::rows(
            "SELECT * FROM settings"
        );
    }
}
