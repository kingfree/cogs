<?php
/**
 * 功能权限按历史编号存储。入口用中文权限名发起检查，登录会话保存该用户最近一次读取的授权。
 * 例：$mayEdit = Privilege::allows('修改题目');
 */
final class Privilege
{
    // 这些编号已写入旧库，改名时仍要保留既有编号；拥有比赛与上传数据共用 33。
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

    /** 读取单个用户的授权，供 Login 按次请求刷新。 */
    public static function forUser($userId)
    {
        return Database::rows(
            "SELECT * FROM privilege WHERE uid=? ORDER BY pri",
            [$userId]
        );
    }

    /** 添加权限。锁住目标账号来串行化重复发放；旧库没有 (uid, pri) 的唯一索引。 */
    public static function grant(int $userId, int $privilege): bool
    {
        $db = Database::connection();
        $db->beginTransaction(); // 账号行既确认接收者存在，也是该用户各次授予操作共享的锁。
        try {
            $user = Database::rows('SELECT uid FROM userinfo WHERE uid=? FOR UPDATE', [$userId]);
            if (!$user) throw new InvalidArgumentException('无此用户！'); // 拒绝发给不存在账号的授权，避免留下无主记录。
            $added = Database::execute('INSERT INTO privilege(uid, pri, def)
                SELECT ?, ?, 1 WHERE NOT EXISTS (SELECT 1 FROM privilege WHERE uid=? AND pri=?)',
                [$userId, $privilege, $userId, $privilege]); // 重复点击不会给同一用户写出第二条同名授权。
            $db->commit();
            return $added > 0; // 只有本次实际新增时才提示已授予。
        } catch (Throwable $error) {
            $db->rollBack();
            throw $error; // 表单输入错误和数据库故障分别由各自的调用边界处理。
        }
    }

    /** 只撤销匹配账号和编号的显式授权；角色继承仍由 allows 计算。 */
    public static function revoke($userId, $privilege)
    {
        return Database::execute(
            "DELETE FROM `privilege` WHERE uid=? AND pri=?",
            [$userId, $privilege]
        );
    }

    /** 后台按用户和编号展示已有授权。 */
    public static function holders()
    {
        return Database::rows(
            "SELECT privilege.*, userinfo.email, userinfo.nickname
             FROM privilege, userinfo
             WHERE userinfo.uid=privilege.uid
             ORDER BY uid, pri ASC"
        );
    }

    /** 在当前请求的会话上判定一项能力；管理用户继承编号低于 60 的权限，超级用户继承所有已知权限。 */
    public static function allows($name): bool
    {
        if ($name === '' || $name === null) return true; // 现有公开入口用空权限表示允许匿名访问。
        if ($name === '普通用户') return !empty($_SESSION['ID']); // 登录身份本身即可进入个人面板等页面。
        // 后台菜单使用这个汇总判断；执行动作的页仍应核对该动作的具体权限。
        if ($name === '可以管理') {
            foreach (self::NAMES as $number) if ($number && !empty($_SESSION['pri'.$number])) return true;
            return false;
        }
        if (!isset(self::NAMES[$name])) return false; // 未注册权限名称不能默认为允许。
        $number = self::NAMES[$name]; // 先转换为旧库编号，才能核对登录会话里的授权。
        return !empty($_SESSION['pri'.$number]) || !empty($_SESSION['pri2']) ||
            ($number < 60 && !empty($_SESSION['pri1']));
    }

    /** 批量读取账号权限，返回以用户 ID 为键的授权列表。 */
    public static function forUsers(array $ids): array
    {
        if (!$ids) return []; // 空结果页无需生成空的 IN 条件。
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $rows = Database::rows("SELECT uid, pri, def FROM privilege WHERE uid IN ($marks) ORDER BY pri", $ids); // 一次取齐当前页用户的显式授权。
        $byUser = [];
        foreach ($rows as $row) $byUser[$row['uid']][] = $row; // 以 UID 分组，模板能直接显示对应用户的权限。
        return $byUser;
    }

}
