<?php
/**
 * 分组继承沿 parent 字段向上追溯。用成员所在组查它能读什么；用目标组查哪些下属组可参加比赛。
 * 例：$mayEnter = UserGroup::includes(2, (int)$_SESSION['group']);
 */
final class UserGroup
{
    /** 按组名列出全部分组。 */
    public static function all()
    {
        return Database::rows(
            "SELECT * FROM groups ORDER BY gname"
        );
    }

    /** 新组保存管理员 UID 与上级组编号。 */
    public static function create($name, $memo, $owner, $parent)
    {
        return Database::execute(
            "INSERT INTO groups(gname, memo, adminuid, parent) VALUES(?, ?, ?, ?)",
            [$name, $memo, $owner, $parent]
        );
    }

    /** 更新组名、说明、管理员和上级，组编号保持不变。 */
    public static function edit($name, $memo, $owner, $parent, $id)
    {
        return Database::execute(
            "UPDATE groups SET gname=?, memo=?, adminuid=?, parent=? WHERE gid=?",
            [$name, $memo, $owner, $parent, $id]
        );
    }

    /** 按组编号取得分组信息。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM groups WHERE gid=?",
            [$id]
        );
    }

    /** 编辑页使用完整分组列表填充选项。 */
    public static function choices()
    {
        return Database::rows(
            "SELECT * FROM groups"
        );
    }

    /** 组列表同时显示管理员昵称，按组名排列。 */
    public static function withManagers()
    {
        return Database::rows(
            "SELECT groups.*, userinfo.uid, userinfo.nickname
             FROM groups, userinfo
             WHERE groups.adminuid=userinfo.uid
             ORDER BY gname"
        );
    }

    /** 每次请求加载一次组树，按组 ID 建索引以便逐级寻找父组。 */
    public static function tree(): array
    {
        static $tree;
        return $tree ??= array_column(self::all(), null, 'gid');
    }

    /** 返回所有下属组的 ID，不包含起点本身；已遍历的组不会因旧数据中的环再次入队。 */
    public static function descendants($id): array
    {
        $children = [];
        foreach (self::tree() as $group) $children[$group['parent']][] = (int)$group['gid'];
        $queue = [(int)$id];
        $seen = [(int)$id => true];
        for ($i = 0; $i < count($queue); $i++) {
            foreach ($children[$queue[$i]] ?? [] as $child) {
                if (isset($seen[$child])) continue;
                $seen[$child] = true;
                $queue[] = $child;
            }
        }
        return array_slice($queue, 1);
    }

    /** 列出当前成员能继承的组 ID，含公开组 0、成员自己的组和各级上级组。 */
    public static function ancestors(int $member): array
    {
        $groups = self::tree();
        // 公开组始终可见；若旧库存在断开的父节点或循环，沿父链查询会在这里收敛。
        $seen = [0 => true];
        while ($member && isset($groups[$member]) && !isset($seen[$member])) {
            $seen[$member] = true;
            $member = (int)$groups[$member]['parent'];
        }
        return array_keys($seen);
    }

    /** 判断目标组是否向该成员开放，包括公开组、同组或下级组成员。 */
    public static function includes($parent, $member): bool
    {
        return (int)$parent === 0 || (int)$member === (int)$parent ||
            in_array((int)$member, self::descendants($parent), true);
    }

}
