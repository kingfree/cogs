<?php
// 账号权限的授予和撤销。例如 Privilege::forUser(1)。
final class Privilege
{
    public static function forUser($userId)
    {
        return Database::rows(
            "SELECT * FROM privilege WHERE uid=?",
            [$userId]
        );
    }

    public static function ordered($userId)
    {
        return Database::rows(
            "SELECT privilege.* FROM privilege WHERE uid=? ORDER BY pri ASC",
            [$userId]
        );
    }

    public static function granted($userId, $privilege)
    {
        return Database::rows(
            "SELECT def FROM `privilege` WHERE uid=? AND pri=? LIMIT 1",
            [$userId, $privilege]
        );
    }

    public static function grant($userId, $privilege)
    {
        return Database::execute(
            "INSERT INTO `privilege`(uid, pri, def) VALUES(?, ?,'1')",
            [$userId, $privilege]
        );
    }

    public static function revoke($userId, $privilege)
    {
        return Database::execute(
            "DELETE FROM `privilege` WHERE uid=? AND pri=?",
            [$userId, $privilege]
        );
    }

    public static function holders()
    {
        return Database::rows(
            "SELECT privilege.*, userinfo.email, userinfo.nickname
             FROM privilege, userinfo
             WHERE userinfo.uid=privilege.uid
             ORDER BY uid, pri ASC"
        );
    }

public const NAMES = [
        '普通用户' => 0,
        '管理用户' => 1,
        '超级用户' => 2,
        '查看代码' => 21,
        '修改题目' => 11,
        '查看题目' => 12,
        '查看数据' => 34,
        '测试题目' => 14,
        '修改页面' => 15,
        '查看页面' => 16,
        '查看比赛' => 31,
        '修改比赛' => 32,
        '拥有比赛' => 33,
        '发表评论' => 51,
        '管理评论' => 52,
        '上传数据' => 33,
        '修改用户' => 41,
        '查看用户' => 45,
        '生成等级' => 43,
        '分组管理' => 44,
        '分类管理' => 42,
        '管理评测' => 61,
        '修改权限' => 72,
        '参数设置' => 99,
    ];

    public static function allows($name): bool
    {
        if ($name === '' || $name === null) return true;
        if ($name === '普通用户') return !empty($_SESSION['ID']);
        if ($name === '可以管理') {
            foreach (self::NAMES as $number) if ($number && !empty($_SESSION['pri'.$number])) return true;
            return false;
        }
        if (!isset(self::NAMES[$name])) return false;
        $number = self::NAMES[$name];
        return !empty($_SESSION['pri'.$number]) || !empty($_SESSION['pri2']) ||
            ($number < 60 && !empty($_SESSION['pri1']));
    }

    public static function forUsers(array $ids): array
    {
        if (!$ids) return [];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $rows = Database::rows("SELECT uid, pri, def FROM privilege WHERE uid IN ($marks) ORDER BY pri", $ids);
        $byUser = [];
        foreach ($rows as $row) $byUser[$row['uid']][] = $row;
        return $byUser;
    }

}
