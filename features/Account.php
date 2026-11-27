<?php
/**
 * 账号资料及凭据保存在 userinfo。user/dologin.php 先取账号，再交给 Password 核验。
 * 例：$user = Account::byUsername('student')[0] ?? null;
 */
final class Account
{
    /** 注册和个人面板共用用户名、昵称、姓名及邮箱的规则。返回清理过的资料，错误可直接提示用户。 */
    public static function profileFields(array $input): array
    {
        $profile = []; // 从两份表单中只接收可自行变更的资料，忽略权限、分组等字段。
        foreach (['usr', 'nickname', 'realname', 'email', 'memo'] as $field) {
            $profile[$field] = trim($input[$field] ?? ''); // 姓名和邮箱去掉复制带来的空白，密码不经过此步骤。
        }
        if (!preg_match('/^[A-Za-z0-9_]{1,24}$/D', $profile['usr']) ||
            $profile['nickname'] === '' || mb_strlen($profile['nickname']) > 16 ||
            mb_strlen($profile['realname']) > 16 || !filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('用户名、昵称或邮箱格式无效。');
        }
        return $profile; // 注册和个人面板拿到相同的字段与长度约束。
    }

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

    /** 按编号、昵称或登录名找用户；“查看用户”同时控制按真实姓名查找和显示。 */
    public static function findProfile(string $keyword): array
    {
        $condition = 'u.uid=? OR u.nickname=? OR u.usr=?';
        $id = ctype_digit($keyword) ? (int)$keyword : null; // MySQL 会把以数字开头的用户名误当成部分匹配的 UID。
        $values = [$id, $keyword, $keyword];
        if (Privilege::allows('查看用户')) {
            $condition .= ' OR u.realname=?'; // 未获授权的读者也不能借命中结果反查姓名。
            $values[] = $keyword;
        }
        return Database::rows("SELECT u.*, g.gname, g.gid FROM userinfo u JOIN groups g ON u.gbelong=g.gid
            WHERE $condition", $values);
    }

    /** 注册与修改资料都依靠唯一索引处理同名请求，包括两个表单同时提交的情况。 */
    private static function writeUsername(string $sql, array $values): void
    {
        try {
            Database::execute($sql, $values); // 让唯一索引判断用户名是否冲突，包括同时到达的注册请求。
        } catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) == 1062) throw new InvalidArgumentException('用户名已存在。'); // 1062 专指重名，磁盘或数据库连接故障要向上传递。
            throw $error;
        }
    }

    /** 保存面板资料及可选背景。先验证和转码，用户名冲突时原图仍保留。 */
    public static function editProfile(int $id, array $profile, string $theme, bool $darkHeader, ?array $upload): void
    {
        $temporary = null; // 未上传背景时单独保存文字资料。
        $directory = publicFilePath('images/background'); // 只用会话里的账号编号命名，上传文件名不作为路径。
        if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) { // PHP 会为未选择文件的控件传入 UPLOAD_ERR_NO_FILE。
            if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name']) || $upload['size'] > 4194304) {
                throw new InvalidArgumentException('背景图片格式或大小无效。');
            }
            $info = @getimagesize($upload['tmp_name']); // 超过 1200 万像素时，在完整解码之前拒绝。
            if (!$info || $info[0] * $info[1] > 12000000 || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF], true)) {
                throw new InvalidArgumentException('背景图片格式或大小无效。');
            }
            $image = @imagecreatefromstring(file_get_contents($upload['tmp_name'])); // 公开目录只存重新编码后的 PNG。
            if (!$image) throw new InvalidArgumentException('背景图片格式无效。');
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) throw new RuntimeException('无法建立背景图片目录。');
            $temporary = tempnam($directory, '.background-'); // 和最终图片放在同一目录，发布时可原子替换。
            if ($temporary === false) throw new RuntimeException('背景图片临时文件创建失败。');
        }

        try {
            if ($temporary !== null && !imagepng($image, $temporary)) throw new RuntimeException('背景图片保存失败。');
            self::saveProfile($id, $profile, $theme, $darkHeader, $temporary); // 临时文件仍由本次请求负责清理。
        } finally {
            if ($temporary !== null && is_file($temporary)) unlink($temporary);
        }
    }

    /** 将已转码的背景与账号资料一并发布。失败时恢复上次发布的图片，再让下一位修改者进入。 */
    private static function saveProfile(int $id, array $profile, string $theme, bool $darkHeader, ?string $image): void
    {
        $db = Database::connection();
        $lock = null;
        $previous = null; // 原来没有背景时，回滚直接删除刚发布的图片。
        $published = false; // 只有新图已发布时才需要恢复旧版本。
        if ($image !== null) {
            $directory = dirname($image);
            $destination = "$directory/$id.png"; // 旧链接与浏览器缓存一直使用这个公开地址。
            $lock = fopen("$directory/.$id.lock", 'c'); // 所有修改同一账号背景的请求使用同一把锁。
            if (!$lock) throw new RuntimeException('无法打开背景图片锁。');
        }
        try {
            if ($lock && !flock($lock, LOCK_EX)) throw new RuntimeException('无法锁定背景图片。');
            $db->beginTransaction(); // 文件锁的有效期覆盖数据库提交失败后的恢复阶段。
            self::writeUsername(
                "UPDATE userinfo SET usr=?, nickname=?, realname=?, email=?, memo=?, user_style=?, style=? WHERE uid=?",
                [$profile['usr'], $profile['nickname'], $profile['realname'], $profile['email'], $profile['memo'], $theme, (int)$darkHeader, $id]
            );
            if ($image !== null) {
                if (is_file($destination)) { // 首次上传无需备份，回滚会恢复为无背景。
                    $previous = tempnam($directory, '.previous-'); // 替换前留下一份可直接改名恢复的旧图。
                    if (!$previous || !copy($destination, $previous)) throw new RuntimeException('无法备份上次背景图片。');
                }
                if (!rename($image, $destination)) throw new RuntimeException('背景图片保存失败。');
                $published = true; // 仅在真正覆盖公开地址后，失败处理才需要恢复。
            }
            $db->commit(); // 数据库拒绝提交时，下方恢复本次更改过的公开文件。
        } catch (Throwable $error) {
            if ($published) {
                $restored = $previous ? rename($previous, $destination) : unlink($destination); // 已有旧图时覆盖回去；首次上传时撤回刚发布的新图。
                if (!$restored) {
                    // 保留备份以便人工恢复；磁盘发生第二次故障时不能再承诺已经还原。
                    $backupPath = $previous;
                    $previous = null; // 阻止 finally 清除磁盘仍保存着的最后一份旧图。
                    throw new RuntimeException('背景图片恢复失败，备份位置：'.$backupPath, 0, $error);
                }
            }
            throw $error;
        } finally {
            try {
                if ($db->inTransaction()) $db->rollBack(); // 在释放图片锁之前结束尚未提交的资料变更。
                if ($previous && is_file($previous)) unlink($previous);
            } finally {
                if ($lock) fclose($lock); // 锁文件保留供下一请求复用，关闭句柄释放当前持有的锁。
            }
        }
    }

    /** 个人面板修改密码或找回问题前，先取当前密码哈希核验。 */
    public static function credentials($userId)
    {
        return Database::rows(
            "SELECT pwdhash FROM userinfo WHERE uid=?",
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

    /** 面板验证旧密码后更换找回问题。并发改密后，本次旧凭据不能继续写入。 */
    public static function changeVerifiedRecovery(int $id, string $passwordHash, array $recovery): bool
    {
        $changed = Database::execute('UPDATE userinfo SET pwdtipques=?, pwdtipanshash=? WHERE uid=? AND pwdhash=?',
            [$recovery['question'], $recovery['answerHash'], $id, $passwordHash]);
        // 重复关闭找回可能不改变任何字段；凭据版本仍有效时这也是一次成功操作。
        return $changed > 0 || (bool)Database::rows('SELECT uid FROM userinfo WHERE uid=? AND pwdhash=?', [$id, $passwordHash]);
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

    /** 新账号继承站点默认分组、阅读等级与主题。返回新会话所需的账号 ID 和密码版本来源。 */
    public static function register(array $profile, string $hash, array $recovery, array $settings): array
    {
        // reg_readfroce 是早期数据库中的拼写，升级旧站时仍可沿用原配置。
        $readLevel = (int)($settings['reg_readforce'] ?? $settings['reg_readfroce'] ?? 0);
        self::writeUsername(
            "INSERT INTO userinfo(usr, nickname, readforce, admin, regtime, pwdhash, pwdtipques, pwdtipanshash,
                memo, realname, email, gbelong, user_style, style) VALUES(?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)",
            [$profile['usr'], $profile['nickname'], $readLevel, time(), $hash, $recovery['question'], $recovery['answerHash'],
             $profile['memo'], $profile['realname'], $profile['email'], $settings['reg_defgroup'], $settings['user_style']]
        );
        return ['uid'=>(int)Database::connection()->lastInsertId(), 'pwdhash'=>$hash]; // 用数据库分配的 UID 建立新会话，不再按可能同名的昵称查找。
    }

    /** 找回第一步只向页面提供提示问题。null 表示用户不存在，空字符串表示未设问题。 */
    public static function recoveryQuestion(string $username): ?string
    {
        return Database::rows('SELECT pwdtipques FROM userinfo WHERE usr=?', [$username])[0]['pwdtipques'] ?? null;
    }

    /** 核对答案并颁发临时密码。更改期间凭据发生变化时，用户需重新验证。 */
    public static function recoverPassword(string $username, string $answer): ?string
    {
        $account = Database::rows('SELECT uid, pwdhash, pwdtipanshash FROM userinfo WHERE usr=?', [$username])[0] ?? null;
        if (!$account || !Password::verify($answer, $account['pwdtipanshash'])) return null; // 账号缺失和回答不符走相同的拒绝路径。
        $temporary = Password::temporary(); // 仅在答案正确之后产生替换原密码的凭据。
        $changed = Database::execute(
            'UPDATE userinfo SET pwdhash=? WHERE uid=? AND pwdhash=? AND pwdtipanshash=?',
            [Password::hash($temporary), $account['uid'], $account['pwdhash'], $account['pwdtipanshash']]
        );
        return $changed ? $temporary : null; // 更新真正生效后才展示明文临时密码。
    }

    /** 在指定分组内检索用户。通过率排序处理零次提交，同分使用 UID 稳定排序。 */
    public static function search(array $groups, $key, $sort, $order)
    {
        $marks = implode(',', array_fill(0, count($groups), '?')); // 下级分组数量随组树变化，每组占用一个绑定位置。
        $sql = "SELECT u.*, g.gname FROM userinfo u JOIN groups g ON g.gid=u.gbelong WHERE u.gbelong IN ($marks)";
        $values = array_values($groups); // 后面追加关键词时保留分组条件的占位顺序。
        $canViewNames = Privilege::allows('查看用户');
        if ($key !== '') {
            $pattern = '%'.$key.'%'; // UID 精确匹配，登录名和昵称允许按片段查找。
            $filter = 'u.nickname LIKE ? OR u.uid=? OR u.usr LIKE ?';
            $id = ctype_digit($key) ? (int)$key : null; // 编号只能完整匹配，数字开头的昵称仍按文字搜索。
            array_push($values, $pattern, $id, $pattern);
            if ($canViewNames) {
                $filter .= ' OR u.realname LIKE ?'; // 不能通过反复搜索推断受保护的姓名。
                $values[] = $pattern;
            }
            $sql .= " AND ($filter)";
        }
        // 排序字段属于 SQL 结构，只有下列名称能参与排序。分组、关键词等值已经单独绑定。
        $columns = ['uid', 'nickname', 'readforce', 'gbelong', 'accepted', 'grade'];
        if ($canViewNames) $columns[] = 'realname'; // 排序也遵守展示真实姓名的权限。
        $column = in_array($sort, $columns, true) ? 'u.'.$sort : 'u.grade';
        if ($sort === 'accepted/submited') $column = 'u.accepted/NULLIF(u.submited, 0)'; // 尚无提交时通过率分母为空，避免除零。
        $direction = strtolower($order) === 'asc' ? 'ASC' : 'DESC'; // 排序方向是 SQL 结构，只允许这两个常量。
        return Database::rows($sql." ORDER BY $column $direction, u.uid", $values);
    }

    /** 仅当核对过的旧哈希仍然有效才写入新密码，避免并发改密互相覆盖。返回值 0 表示未更新。 */
    public static function replaceVerifiedPassword($hash, $id, $previous)
    {
        return Database::execute("UPDATE userinfo SET pwdhash=? WHERE uid=? AND pwdhash=?", [$hash, $id, $previous]);
    }
}
