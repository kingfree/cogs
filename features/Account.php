<?php
/**
 * 账号资料及凭据保存在 userinfo。user/dologin.php 先取账号，再交给 Password 核验。
 * 例：$user = Account::byUsername('student')[0] ?? null;
 */
final class Account
{
    /** 列出用户 ID、登录名和真实姓名，调用方负责限制真实姓名的展示。 */
    public static function directory()
    {
        return Database::rows(
            "SELECT uid, usr, realname FROM userinfo"
        );
    }

    /** 按总积分排序，供站点地图链接到选手。 */
    public static function rankDirectory()
    {
        return Database::rows(
            "SELECT uid, usr, nickname FROM userinfo ORDER BY grade DESC"
        );
    }

    /** 读取完整账号记录；可能包含凭据字段，调用方应选取所需字段。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM userinfo WHERE uid=?",
            [$id]
        );
    }

    /** 取首页指定数量的高分选手。 */
    public static function leaders($limit)
    {
        return Database::rows(
            "SELECT * FROM userinfo ORDER BY grade DESC LIMIT 0, ?",
            [$limit]
        );
    }

    /** 取题目作者的真实姓名，查无记录返回空列表。 */
    public static function realName($userId)
    {
        return Database::rows(
            "SELECT realname FROM userinfo WHERE uid =? LIMIT 1",
            [$userId]
        );
    }

    /** 用登录名查 ID，供注册页检查重名。 */
    public static function byUsernameId($username)
    {
        return Database::rows(
            "SELECT uid FROM userinfo WHERE usr=?",
            [$username]
        );
    }

    /** 个人页按账号 ID 读取资料及当前分组。 */
    public static function profile($userId)
    {
        return Database::rows(
            "SELECT userinfo.*, groups.gname, groups.gid
             FROM userinfo, groups
             WHERE userinfo.uid=? AND userinfo.gbelong=groups.gid",
            [$userId]
        );
    }

    /** 用户搜索可使用 ID、昵称、真实姓名或登录名。 */
    public static function findProfile($id, $nickname, $realname, $username)
    {
        return Database::rows(
            "SELECT userinfo.*, groups.gname, groups.gid
             FROM userinfo, groups
             WHERE (userinfo.uid=? OR userinfo.nickname=? OR userinfo.realname=? OR userinfo.usr=?) AND userinfo.gbelong=groups.gid",
            [$id, $nickname, $realname, $username]
        );
    }

    /** 更换登录名只检查其他账号；保留自己原来的名称是合法操作。 */
    public static function usernameInUse($username, $exceptId)
    {
        return Database::rows(
            "SELECT * FROM userinfo WHERE usr=? AND uid!=?",
            [$username, $exceptId]
        );
    }

    /** 保存用户能自行修改的资料，账号来自登录会话。 */
    public static function editProfile($username, $nickname, $realName, $email, $memo, $theme, $style, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET usr=?, nickname=?, realname=?, email=?, memo=?, user_style=?, style=?
             WHERE uid=?",
            [$username, $nickname, $realName, $email, $memo, $theme, $style, $id]
        );
    }

    /** 个人面板修改密码或找回问题前，先取当前密码哈希核验。 */
    public static function credentials($userId)
    {
        return Database::rows(
            "SELECT pwdhash, nickname FROM userinfo WHERE uid=?",
            [$userId]
        );
    }

    /** 用已生成的密码哈希更新指定 UID。会话在下次加载时核对新哈希。 */
    public static function changePassword($hash, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET pwdhash=? WHERE uid=?",
            [$hash, $id]
        );
    }

    /** 保存提示问题与答案哈希；空答案表示未设置找回凭据。 */
    public static function recovery($question, $answer, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET pwdtipques=?, pwdtipanshash=? WHERE uid=?",
            [$question, $answer, $id]
        );
    }

    /** 读取旧版账号管理标记。功能权限由 Privilege 判断。 */
    public static function role($id)
    {
        return Database::rows(
            "SELECT admin, uid FROM userinfo WHERE uid=?",
            [$id]
        );
    }

    /** 删除指定账号的 userinfo 记录；调用入口需先确认管理权限。 */
    public static function remove($id)
    {
        return Database::execute(
            "DELETE FROM userinfo WHERE uid=?",
            [$id]
        );
    }

    /** 管理员编辑用户资料、阅读等级及所属分组。 */
    public static function administer($nickname, $readLevel, $email, $memo, $realName, $groupId, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET nickname=? , readforce=? , email=?, memo=?, realname=?, gbelong=? WHERE uid=?",
            [$nickname, $readLevel, $email, $memo, $realName, $groupId, $id]
        );
    }

    /** 登录时按唯一登录名精确匹配，未找到返回空列表。 */
    public static function byUsername($username)
    {
        return Database::rows(
            "SELECT * FROM userinfo WHERE usr = ? LIMIT 1",
            [$username]
        );
    }

    /** 新账号的分组及阅读等级来自站点设置；密码和答案应已由 Password 处理。 */
    public static function register($username, $nickname, $readLevel, $time, $hash, $question, $answer, $memo, $realName, $email, $groupId, $theme)
    {
        return Database::execute(
            "INSERT INTO userinfo(usr, nickname, readforce, admin, regtime, pwdhash, pwdtipques, pwdtipanshash, memo, realname, email, gbelong, user_style, style) VALUES(?, ?, ?,0, ?, ?, ?, ?, ?, ?, ?, ?, ?,0)",
            [$username, $nickname, $readLevel, $time, $hash, $question, $answer, $memo, $realName, $email, $groupId, $theme]
        );
    }

    /** 找回密码第一步只返回可展示的提示问题。 */
    public static function recoveryQuestion($username)
    {
        return Database::rows(
            "SELECT pwdtipques FROM userinfo WHERE usr=?",
            [$username]
        );
    }

    /** 找回密码第二步取答案哈希，由 Password::verify 核验输入。 */
    public static function recoveryAnswer($username)
    {
        return Database::rows(
            "SELECT pwdtipanshash FROM userinfo WHERE usr=?",
            [$username]
        );
    }

    /** 按登录名保存新密码哈希，供已通过答案核验的重置流程调用。 */
    public static function resetPassword($hash, $username)
    {
        return Database::execute(
            "UPDATE userinfo SET pwdhash=? WHERE usr=?",
            [$hash, $username]
        );
    }

    /** 在指定分组内检索用户。通过率排序处理零次提交，同分使用 UID 稳定排序。 */
    public static function search(array $groups, $key, $sort, $order)
    {
        $marks = implode(',', array_fill(0, count($groups), '?'));
        $sql = "SELECT u.*, g.gname FROM userinfo u JOIN groups g ON g.gid=u.gbelong WHERE u.gbelong IN ($marks)";
        $values = array_values($groups);
        if ($key !== '') {
            $sql .= " AND (u.nickname LIKE ? OR u.uid=? OR u.usr LIKE ? OR u.realname LIKE ?)";
            array_push($values, '%'.$key.'%', $key, '%'.$key.'%', '%'.$key.'%');
        }
        // 排序字段属于 SQL 结构，只有下列名称能参与排序。分组、关键词等值已经单独绑定。
        $columns = ['uid', 'nickname', 'realname', 'readforce', 'gbelong', 'accepted', 'grade'];
        $column = in_array($sort, $columns, true) ? 'u.'.$sort : 'u.grade';
        if ($sort === 'accepted/submited') $column = 'u.accepted/NULLIF(u.submited, 0)';
        $direction = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        return Database::rows($sql." ORDER BY $column $direction, u.uid", $values);
    }

    /** 仅当旧哈希仍与登录时一致才升级，避免覆盖同时发生的改密。返回值 0 表示未更新。 */
    public static function upgradePassword($hash, $id, $previous)
    {
        return Database::execute("UPDATE userinfo SET pwdhash=? WHERE uid=? AND pwdhash=?", [$hash, $id, $previous]);
    }
}
