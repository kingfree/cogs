<?php
/**
 * 站点设置按名称区分用途，后台按固定编号保存。查看一项：$setting = Setting::find(0, 'style_pagesize');
 * 启动请求使用 all() 读出全部配置，保留数据库中的原始值，交给使用该配置的功能解释。
 */
final class Setting
{
    /** 旧链接使用 ssid，新链接也可用名称。两者都有时以编号为准；找不到返回 null。 */
    public static function find(int $id, string $name = ''): ?array
    {
        if ($id) return Database::rows('SELECT ssid, name, value FROM settings WHERE ssid=?', [$id])[0] ?? null;  // 旧编辑页用非零 ssid 精确定位配置。
        return Database::rows('SELECT ssid, name, value FROM settings WHERE name=?', [$name])[0] ?? null;  // 没有编号时按完整名称查找，旧书签仍能打开。
    }

    /** 仅修改已有配置的值。重复提交原值也是成功保存，未知编号仍返回 false。 */
    public static function change(string $value, int $id): bool
    {
        $changes = Database::execute('UPDATE settings SET value=? WHERE ssid=?', [$value, $id]);  // 按编号绑定新值，空字符串也是允许保存的配置。
        return $changes > 0 || self::find($id) !== null;  // 相同值的 UPDATE 影响行数为零，要再区分配置仍在或已被删除。
    }

    /** 按编号列出设置，例如管理页编辑链接使用 ssid，请求启动时以 name 建立键值表。 */
    public static function all(): array
    {
        return Database::rows('SELECT ssid, name, value FROM settings ORDER BY ssid');  // 按稳定的编号顺序返回名称和值，后台列表和站点启动共用这一结果。
    }
}
