<?php
// 用户资料、注册和登录凭据。例如 Account::directory()。
final class Account
{
    public static function directory()
    {
        return Database::rows(
            "SELECT uid, usr, realname FROM userinfo"
        );
    }

    public static function rankDirectory()
    {
        return Database::rows(
            "SELECT uid, usr, nickname FROM userinfo ORDER BY grade DESC"
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM userinfo WHERE uid=?",
            [$id]
        );
    }

    public static function leaders($limit)
    {
        return Database::rows(
            "SELECT * FROM userinfo ORDER BY grade DESC LIMIT 0, ?",
            [$limit]
        );
    }

    public static function realName($userId)
    {
        return Database::rows(
            "SELECT realname FROM userinfo WHERE uid =? LIMIT 1",
            [$userId]
        );
    }

    public static function byUsernameId($username)
    {
        return Database::rows(
            "SELECT uid FROM userinfo WHERE usr=?",
            [$username]
        );
    }

    public static function profile($userId)
    {
        return Database::rows(
            "SELECT userinfo.*, groups.gname, groups.gid
             FROM userinfo, groups
             WHERE userinfo.uid=? AND userinfo.gbelong=groups.gid",
            [$userId]
        );
    }

    public static function findProfile($id, $nickname, $realname, $username)
    {
        return Database::rows(
            "SELECT userinfo.*, groups.gname, groups.gid
             FROM userinfo, groups
             WHERE (userinfo.uid=? OR userinfo.nickname=? OR userinfo.realname=? OR userinfo.usr=?) AND userinfo.gbelong=groups.gid",
            [$id, $nickname, $realname, $username]
        );
    }

    public static function usernameInUse($username, $exceptId)
    {
        return Database::rows(
            "SELECT * FROM userinfo WHERE usr=? AND uid!=?",
            [$username, $exceptId]
        );
    }

    public static function editProfile($username, $nickname, $realName, $email, $memo, $theme, $style, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET usr=?, nickname=?, realname=?, email=?, memo=?, user_style=?, style=?
             WHERE uid=?",
            [$username, $nickname, $realName, $email, $memo, $theme, $style, $id]
        );
    }

    public static function credentials($userId)
    {
        return Database::rows(
            "SELECT pwdhash, nickname FROM userinfo WHERE uid=?",
            [$userId]
        );
    }

    public static function changePassword($hash, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET pwdhash=? WHERE uid=?",
            [$hash, $id]
        );
    }

    public static function recovery($question, $answer, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET pwdtipques=?, pwdtipanshash=? WHERE uid=?",
            [$question, $answer, $id]
        );
    }

    public static function role($id)
    {
        return Database::rows(
            "SELECT admin, uid FROM userinfo WHERE uid=?",
            [$id]
        );
    }

    public static function remove($id)
    {
        return Database::execute(
            "DELETE FROM userinfo WHERE uid=?",
            [$id]
        );
    }

    public static function administer($nickname, $readLevel, $email, $memo, $realName, $groupId, $id)
    {
        return Database::execute(
            "UPDATE userinfo SET nickname=? , readforce=? , email=?, memo=?, realname=?, gbelong=? WHERE uid=?",
            [$nickname, $readLevel, $email, $memo, $realName, $groupId, $id]
        );
    }

    public static function byUsername($username)
    {
        return Database::rows(
            "SELECT * FROM userinfo WHERE usr = ? LIMIT 1",
            [$username]
        );
    }

    public static function register($username, $nickname, $readLevel, $time, $hash, $question, $answer, $memo, $realName, $email, $groupId, $theme)
    {
        return Database::execute(
            "INSERT INTO userinfo(usr, nickname, readforce, admin, regtime, pwdhash, pwdtipques, pwdtipanshash, memo, realname, email, gbelong, user_style, style) VALUES(?, ?, ?,0, ?, ?, ?, ?, ?, ?, ?, ?, ?,0)",
            [$username, $nickname, $readLevel, $time, $hash, $question, $answer, $memo, $realName, $email, $groupId, $theme]
        );
    }

    public static function recoveryQuestion($username)
    {
        return Database::rows(
            "SELECT pwdtipques FROM userinfo WHERE usr=?",
            [$username]
        );
    }

    public static function recoveryAnswer($username)
    {
        return Database::rows(
            "SELECT pwdtipanshash FROM userinfo WHERE usr=?",
            [$username]
        );
    }

    public static function resetPassword($hash, $username)
    {
        return Database::execute(
            "UPDATE userinfo SET pwdhash=? WHERE usr=?",
            [$hash, $username]
        );
    }

    public static function search(array $groups, $key, $sort, $order)
    {
        $marks = implode(',', array_fill(0, count($groups), '?'));
        $sql = "SELECT u.*, g.gname FROM userinfo u JOIN groups g ON g.gid=u.gbelong WHERE u.gbelong IN ($marks)";
        $values = array_values($groups);
        if ($key !== '') {
            $sql .= " AND (u.nickname LIKE ? OR u.uid=? OR u.usr LIKE ? OR u.realname LIKE ?)";
            array_push($values, '%'.$key.'%', $key, '%'.$key.'%', '%'.$key.'%');
        }
        $columns = ['uid', 'nickname', 'realname', 'readforce', 'gbelong', 'accepted', 'grade'];
        $column = in_array($sort, $columns, true) ? 'u.'.$sort : 'u.grade';
        if ($sort === 'accepted/submited') $column = 'u.accepted/NULLIF(u.submited, 0)';
        $direction = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        return Database::rows($sql." ORDER BY $column $direction, u.uid", $values);
    }

    public static function upgradePassword($hash, $id, $previous)
    {
        return Database::execute("UPDATE userinfo SET pwdhash=? WHERE uid=? AND pwdhash=?", [$hash, $id, $previous]);
    }
}
