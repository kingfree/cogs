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
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT p.*, g.gid, g.gname FROM problem p
            LEFT JOIN groups g ON g.gid=p.`group` WHERE p.pid=? AND $scope", array_merge([$id], $values))[0] ?? null;
    }

    /** 旧下载链接用题目文件名定位。即使链接早已保存，也按本次请求的阅读范围查询。 */
    public static function readableFilename(string $filename): bool
    {
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
    public static function toggle($id)
    {
        return Database::execute(
            "UPDATE problem SET submitable=1-submitable WHERE pid=?",
            [$id]
        );
    }

    /** 登记题目、资源限额及发布属性，数据和分类由编辑入口一并处理。 */
    public static function create($title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $created, $author, $difficulty, $checker, $groupId)
    {
        return Database::execute(
            "INSERT INTO problem(probname, filename, readforce, submitable, datacnt, timelimit, memorylimit, detail, addtime, addid, difficulty, plugin, `group`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $created, $author, $difficulty, $checker, $groupId]
        );
    }

    /** 更新题面及评测参数，原题号及作者保持不变。 */
    public static function edit($title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $difficulty, $checker, $groupId, $id)
    {
        return Database::execute(
            "UPDATE problem SET probname=?, filename=?, readforce=?, submitable=?, datacnt=?, timelimit=?, memorylimit=?, detail=?, difficulty=?, plugin=?, `group`=?
             WHERE pid=?",
            [$title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $difficulty, $checker, $groupId, $id]
        );
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
        $sql = "SELECT p.* FROM problem p WHERE $scope";
        if ($category) {
            $sql .= " AND EXISTS (SELECT 1 FROM tag WHERE tag.pid=p.pid AND tag.caid=?)";
            $values[] = $category;
        }
        if ($key !== '') {
            $sql .= " AND (p.probname LIKE ? OR p.pid=? OR p.filename LIKE ?)";
            array_push($values, '%'.$key.'%', $key, '%'.$key.'%');
        }
        if ($difficulty !== '') {
            $sql .= " AND p.difficulty=?";
            $values[] = $difficulty;
        }
        $columns = ['pid', 'probname', 'filename', 'timelimit', 'memorylimit', 'difficulty', 'plugin',
            'acceptcnt', 'submitcnt', 'submitable'];
        $column = in_array($sort, $columns, true) ? $sort : 'pid';
        $direction = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
        return Database::rows($sql." ORDER BY p.$column $direction", $values);
    }

    /** 按一组明确给定的题号取导出记录，每个 ID 分别绑定。 */
    public static function selected(array $ids)
    {
        if (!$ids) return [];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        return Database::rows("SELECT * FROM problem WHERE pid IN ($marks) ORDER BY pid", $ids);
    }

    /** 累加提交次数；首个通过该题的提交才增加通过人数。 */
    public static function noteSubmission($problemId, $userId, $accepted)
    {
        Database::execute("UPDATE problem SET submitcnt=submitcnt+1,
            lastacid=IF(?, ?, lastacid), acceptcnt=acceptcnt+IF(? AND NOT EXISTS
            (SELECT 1 FROM submit WHERE pid=? AND uid=? AND accepted=1), 1, 0)
            WHERE pid=?", [$accepted, $userId, $accepted, $problemId, $userId, $problemId]);
    }

    /** 在已开放且可读的题目中，随机选取一题该用户尚未通过的题；无候选时返回 null。 */
    public static function randomUnsolved($userId)
    {
        [$scope, $values] = self::visibility();
        $values[] = (int)$userId;
        return Database::rows("SELECT p.pid FROM problem p WHERE p.submitable=1 AND $scope
            AND NOT EXISTS (SELECT 1 FROM submit s WHERE s.pid=p.pid AND s.uid=? AND s.accepted=1)
            ORDER BY RAND() LIMIT 1", $values)[0]['pid'] ?? null;
    }
}
