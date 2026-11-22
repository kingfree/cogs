<?php
// 用户分组和上下级关系。例如 UserGroup::all()。
final class UserGroup
{
    public static function all()
    {
        return Database::rows(
            "SELECT * FROM groups ORDER BY gname"
        );
    }

    public static function create($name, $memo, $owner, $parent)
    {
        return Database::execute(
            "INSERT INTO groups(gname, memo, adminuid, parent) VALUES(?, ?, ?, ?)",
            [$name, $memo, $owner, $parent]
        );
    }

    public static function edit($name, $memo, $owner, $parent, $id)
    {
        return Database::execute(
            "UPDATE groups SET gname=?, memo=?, adminuid=?, parent=? WHERE gid=?",
            [$name, $memo, $owner, $parent, $id]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM groups WHERE gid=?",
            [$id]
        );
    }

    public static function choices()
    {
        return Database::rows(
            "SELECT * FROM groups"
        );
    }

    public static function withManagers()
    {
        return Database::rows(
            "SELECT groups.*, userinfo.uid, userinfo.nickname
             FROM groups, userinfo
             WHERE groups.adminuid=userinfo.uid
             ORDER BY gname"
        );
    }

    // 同一请求中共享这棵分组树；历史数据中的环不会导致递归。
    public static function tree(): array
    {
        static $tree;
        return $tree ??= array_column(self::all(), null, 'gid');
    }

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

    // 分组 0 向所有人开放；其余题目和页面也向该组的所有下级开放。
    public static function ancestors(int $member): array
    {
        $groups = self::tree();
        $seen = [0 => true];
        while ($member && isset($groups[$member]) && !isset($seen[$member])) {
            $seen[$member] = true;
            $member = (int)$groups[$member]['parent'];
        }
        return array_keys($seen);
    }

    public static function includes($parent, $member): bool
    {
        return (int)$parent === 0 || (int)$member === (int)$parent ||
            in_array((int)$member, self::descendants($parent), true);
    }

}
