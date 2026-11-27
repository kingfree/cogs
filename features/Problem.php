<?php
/**
 * 题面、发布状态、出题人和阅读范围来自 problem 表。公开题库、提交和讨论引用同一条阅读规则。
 * 例：$problem = Problem::forReader(1);  // 记录不存在或当前用户不可读时返回 null。
 */
final class Problem
{
    /** 返回 SQL 条件及其绑定参数。查询中把 problem 命名为 p；普通读者只见已开放分组，出题人还可见自己的草稿。 */
    public static function visibility(): array
    {
        $level = (int)$_SESSION['readforce'];
        // 有跨组查看权的账号也必须满足最低阅读等级。
        if (Privilege::allows('查看题目')) return ['p.readforce <= ?', [$level]];
        // 普通用户按组及其父组开放范围检索题目，每个组号占一个绑定位置。
        $groups = UserGroup::ancestors((int)$_SESSION['group']);
        $places = implode(',', array_fill(0, count($groups), '?'));
        // 作者可在发布前打开自己的草稿；访客的 ID 为 0，不应与无作者的旧题相匹配。
        $owner = (int)$_SESSION['ID'];
        return ["p.readforce <= ? AND ((p.addid=? AND p.addid>0) OR (p.submitable=1 AND p.`group` IN ($places)))",
            array_merge([$level, $owner], $groups)];
    }

    /** 读取当前会话可见的题目及分组，缺失或不可见均返回 null。 */
    public static function forReader($id)
    {
        // 将可见性条件合进题号查询，避免未授权读者发现草稿。
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT p.*, g.gid, g.gname FROM problem p
            LEFT JOIN groups g ON g.gid=p.`group` WHERE p.pid=? AND $scope", array_merge([$id], $values))[0] ?? null;
    }

    /** 旧下载链接用题目文件名定位。即使链接早已保存，也按本次请求的阅读范围查询。 */
    public static function readableFilename(string $filename): bool
    {
        // 数据下载使用文件名查找时，同样限制于该会话可见的题目。
        [$scope, $values] = self::visibility();
        return (bool)Database::rows("SELECT p.pid FROM problem p WHERE p.filename=? AND $scope LIMIT 1",
            array_merge([$filename], $values));
    }

    /** 后台选题时列出题目编号、标题和开放标记。 */
    public static function choices()
    {
        return Database::rows(
            "SELECT pid, probname, submitable FROM problem ORDER BY pid"
        );
    }

    /** 按题号读取原始记录，用于已有独立授权的编辑或比赛流程。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM problem WHERE pid=?",
            [$id]
        );
    }

    /** 按通过人数列出当前可读的题目，供站点地图使用。 */
    public static function directory()
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT p.pid, p.probname, p.filename FROM problem p WHERE $scope ORDER BY p.acceptcnt DESC", $values);
    }

    /** 首页列出已发布的可读题目，按加题时间倒序。 */
    public static function recent($limit)
    {
        [$scope, $values] = self::visibility();
        // 首页数量作为整数传给 LIMIT，排序按最近加题时间。
        $values[] = (int)$limit;
        return Database::rows("SELECT p.* FROM problem p WHERE p.submitable=1 AND $scope ORDER BY p.addtime DESC LIMIT ?", $values);
    }

    /** 后台显示最近加入的五道未开放题。 */
    public static function awaitingReview()
    {
        return Database::rows(
            "SELECT * FROM problem WHERE submitable!=1 ORDER BY addtime DESC LIMIT 5"
        );
    }

    /** 按出题人列其未开放的题目。 */
    public static function drafts($author)
    {
        return Database::rows(
            "SELECT * FROM problem WHERE addid=? AND submitable!=1 ORDER BY addtime DESC",
            [$author]
        );
    }

    /** 根据测试数据使用的英文文件名匹配题目编号。 */
    public static function byFilename($filename)
    {
        return Database::rows(
            "SELECT pid FROM problem WHERE filename=?",
            [$filename]
        );
    }

    /** FPS 导入时用标题检查已有题目。 */
    public static function byTitle($title)
    {
        return Database::rows(
            "SELECT pid FROM problem WHERE probname=?",
            [$title]
        );
    }

    /** 切换题目的开放标志，只应在已授权的发布入口调用。 */
    private static function toggle($id)
    {
        return Database::execute(
            "UPDATE problem SET submitable=1-submitable WHERE pid=?",
            [$id]
        );
    }

    /**
     * 保存一道新题或已有题，返回题号。例：Problem::save(0, $_POST, $_FILES['datafile'] ?? [])。
     * 普通出题人可以编辑自己的题。阅读等级、发布状态及特判上传权由当前账号决定。
     */
    public static function save(int $id, array $fields, array $upload): int
    {
        $author = (int)$_SESSION['ID'];
        // 管理权限决定能否发布题目和上传特判，普通作者仅可编辑自己的题。
        $canPublish = Privilege::allows('修改题目');
        $db = Database::connection();
        // 题目字段和分类关联共用同一个数据库事务。
        $db->beginTransaction();
        try {
            if ($id) {
                // 先锁定再判作者，编辑者的归属不会在检查和保存之间变化。
                $problem = Database::rows("SELECT addid FROM problem WHERE pid=? FOR UPDATE", [$id])[0] ?? null;
                if (!$problem || (!$canPublish && $author !== (int)$problem['addid'])) {
                    throw new DomainException('没有修改这道题的权限。', 403);
                }
            }
            // 英文文件名用于测试目录；标题删除前后空白后再检查长度。
            $name = $fields['filename'] ?? '';
            $title = trim($fields['probname'] ?? '');
            // 测试点计数、时间和内存按评测协议使用整数。
            $cases = (int)($fields['datacnt'] ?? 0);
            $milliseconds = (int)($fields['timelimit'] ?? 0);
            $megabytes = (int)($fields['memorylimit'] ?? 0);
            // 旧表单省略评测方式时按普通评测处理。
            $checker = (int)($fields['plugin'] ?? 1);

            // 同时执行题库字段长度与评测机限额检查，避免保存后无法评测的题。
            if (!TestData::name($name) || strlen($name) > 24 || $title === '' || mb_strlen($title) > 50 ||
                $cases < 0 || $cases > GradingLimits::CASES || $milliseconds < 1 || $milliseconds > GradingLimits::TIME_MS ||
                $megabytes < 1 || $megabytes > GradingLimits::MEMORY_MIB || !in_array($checker, [-1,0,1,2,3,4], true)) {
                throw new DomainException('题目名称、测试点数量、评测方式或资源限制无效。', 400);
            }
            // 更新和新建沿用相同字段顺序；普通投稿把阅读等级、发布标记设为零。
            $values = [$title, $name, $canPublish ? (int)($fields['readforce'] ?? 0) : 0,
                (int)($canPublish && !empty($fields['submitable'])), $cases, $milliseconds, $megabytes,
                $fields['detail'] ?? '', (int)($fields['difficulty'] ?? 0), $checker, (int)($fields['group'] ?? 1)];

            // 同一题编辑时可保留原文件名，不同题不能共用一个数据目录名。
            $existing = self::byFilename($name)[0]['pid'] ?? null;
            if ($existing !== null && (int)$existing !== $id) throw new DomainException('题目文件名已使用。', 409);
            if ($id) {
                // 已有题保留最初的作者和加入时间，只更新可编辑字段。
                Database::execute("UPDATE problem SET probname=?, filename=?, readforce=?, submitable=?,
                    datacnt=?, timelimit=?, memorylimit=?, detail=?, difficulty=?, plugin=?, `group`=? WHERE pid=?",
                    [...$values, $id]);
            } else {
                // 首次投稿用当前账号和服务器时间记录创建者。
                Database::execute("INSERT INTO problem (probname,filename,readforce,submitable,datacnt,timelimit,
                    memorylimit,detail,difficulty,plugin,`group`,addtime,addid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [...$values, time(), $author]);
                $id = (int)$db->lastInsertId();             // 分类和数据都挂到这道新题上。
            }
            // 表单中的隐藏零值表示取消勾选，实际关联只取已勾选的键。
            Category::setForProblem($id, array_keys(array_filter($fields['cate'] ?? [])));
            if (!empty($upload['size'])) {
                // 只有实际上传才安装数据，作者本人上传也受判题器权限限制。
                if (!is_uploaded_file($upload['tmp_name'])) throw new DomainException('数据上传失败。');
                TestData::importZip($name, $cases, $upload['tmp_name'], $canPublish, $db->commit(...));
            } else {
                $db->commit();                              // 没有数据包时直接提交题面和分类。
            }
            return $id;
        } catch (Throwable $error) {
            // 撤回本次数据库改动；并发抢占同一文件名与事先查重给出相同提示。
            if ($db->inTransaction()) $db->rollBack();
            if ($error instanceof PDOException && ($error->errorInfo[1] ?? null) === 1062) {
                throw new DomainException('题目标题或文件名已使用。', 409, $error);
            }
            // 其他数据库或文件故障仍交给站点错误处理，不冒充字段冲突。
            throw $error;
        }
    }

    /** 开放或关闭提交，仅允许有发布权限的账号操作存在的题。 */
    public static function changeAvailability(int $id): void
    {
        if (!Privilege::allows('修改题目') || !self::toggle($id)) {
            throw new DomainException('没有修改这道题的权限。', 403);
        }
    }

    /** 按闭区间题号选取题包导出的内容。 */
    public static function between($first, $last)
    {
        return Database::rows(
            "SELECT * FROM problem WHERE pid BETWEEN ? AND ? ORDER BY pid",
            [$first, $last]
        );
    }

    /** 先登记未发布的题目，FPS 导入器据新 ID 分配数据目录。 */
    public static function importTitle($title, $timeLimit, $memoryLimit, $detail, $created, $author, $checker)
    {
        return Database::execute(
            // 导入先登记草稿，测试数据写完后由 setData 补上文件名和点数。
            "INSERT INTO problem(probname, readforce, submitable, datacnt, timelimit, memorylimit, detail, addtime, addid, plugin, `group`) VALUES(?,0,0,0, ?, ?, ?, ?, ?, ?,'0')",
            [$title, $timeLimit, $memoryLimit, $detail, $created, $author, $checker]
        );
    }

    /** 将建好的数据目录名和测试点数写回题目。 */
    public static function setData($cases, $filename, $id)
    {
        return Database::execute(
            "UPDATE problem SET datacnt=?, filename=? WHERE pid=?",
            [$cases, $filename, $id]
        );
    }

    /** 导入图片后更新指定题面内原有的图片引用。 */
    public static function replaceImage($source, $target, $id)
    {
        return Database::execute(
            "UPDATE problem SET detail=replace(detail, ?, ?) WHERE pid=?",
            [$source, $target, $id]
        );
    }

    /** 在当前可见范围内按题目、分类和难度检索；排序只接受本方法列出的列名。 */
    public static function search($category, $key, $difficulty, $sort, $order)
    {
        [$scope, $values] = self::visibility();
        // 先给所有查询加可读范围，之后的分类、关键词和难度只会缩小这个范围。
        $sql = "SELECT p.* FROM problem p WHERE $scope";
        if ($category) {
            $sql .= " AND EXISTS (SELECT 1 FROM tag WHERE tag.pid=p.pid AND tag.caid=?)";
            // 分类号作为值绑定，EXISTS 可避免历史重复标签造成重复题目。
            $values[] = $category;
        }
        if ($key !== '') {
            // 题号精确匹配，标题和英文名允许模糊匹配。
            $sql .= " AND (p.probname LIKE ? OR p.pid=? OR p.filename LIKE ?)";
            array_push($values, '%'.$key.'%', $key, '%'.$key.'%');
        }
        if ($difficulty !== '') {
            // 难度没有传入时不要隐式当作最低等级。
            $sql .= " AND p.difficulty=?";
            $values[] = $difficulty;
        }
        // 排序字段不能当作 SQL 参数，只允许从本站固定字段中选择。
        $columns = ['pid', 'probname', 'filename', 'timelimit', 'memorylimit', 'difficulty', 'plugin',
            'acceptcnt', 'submitcnt', 'submitable'];
        $column = in_array($sort, $columns, true) ? $sort : 'pid';
        $direction = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
        // 方向也从两个常量中选，用户提供的文字始终不会形成 SQL 语句。
        return Database::rows($sql." ORDER BY p.$column $direction", $values);
    }

    /** 按一组明确给定的题号取导出记录，每个 ID 分别绑定。 */
    public static function selected(array $ids)
    {
        if (!$ids) return [];
        // 占位符个数取决于已选题目数量，每个实际题号由数据库单独绑定。
        $marks = implode(',', array_fill(0, count($ids), '?'));
        return Database::rows("SELECT * FROM problem WHERE pid IN ($marks) ORDER BY pid", $ids);
    }

    /** 在已开放且可读的题目中，随机选取一题该用户尚未通过的题；无候选时返回 null。 */
    public static function randomUnsolved($userId)
    {
        [$scope, $values] = self::visibility();
        $values[] = (int)$userId;
        // 排除该用户所有已通过题目，再从余下已发布题目中随机取一题。
        return Database::rows("SELECT p.pid FROM problem p WHERE p.submitable=1 AND $scope
            AND NOT EXISTS (SELECT 1 FROM submit s WHERE s.pid=p.pid AND s.uid=? AND s.accepted=1)
            ORDER BY RAND() LIMIT 1", $values)[0]['pid'] ?? null;
    }
}
