<?php
// 题目、数据限额和发布状态。例如 Problem::forReader($id)。
final class Problem
{
    // 列表、详情、解题和讨论共用这条读取规则。p 是调用方的题目表别名。
    public static function visibility(): array
    {
        $level = (int)$_SESSION['readforce'];
        if (Privilege::allows('查看题目')) return ['p.readforce <= ?', [$level]];
        $groups = UserGroup::ancestors((int)$_SESSION['group']);
        $places = implode(',', array_fill(0, count($groups), '?'));
        $owner = (int)$_SESSION['ID'];
        return ["p.readforce <= ? AND ((p.addid=? AND p.addid>0) OR (p.submitable=1 AND p.`group` IN ($places)))",
            array_merge([$level, $owner], $groups)];
    }

    public static function forReader($id)
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT p.*, g.gid, g.gname FROM problem p
            LEFT JOIN groups g ON g.gid=p.`group` WHERE p.pid=? AND $scope", array_merge([$id], $values))[0] ?? null;
    }

    public static function choices()
    {
        return Database::rows(
            "SELECT pid, probname, submitable FROM problem ORDER BY pid"
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM problem WHERE pid=?",
            [$id]
        );
    }

    public static function directory()
    {
        [$scope, $values] = self::visibility();
        return Database::rows("SELECT p.pid, p.probname, p.filename FROM problem p WHERE $scope ORDER BY p.acceptcnt DESC", $values);
    }

    public static function recent($limit)
    {
        [$scope, $values] = self::visibility();
        $values[] = (int)$limit;
        return Database::rows("SELECT p.* FROM problem p WHERE p.submitable=1 AND $scope ORDER BY p.addtime DESC LIMIT ?", $values);
    }

    public static function awaitingReview()
    {
        return Database::rows(
            "SELECT * FROM problem WHERE submitable!=1 ORDER BY addtime DESC LIMIT 5"
        );
    }

    public static function drafts($author)
    {
        return Database::rows(
            "SELECT * FROM problem WHERE addid=? AND submitable!=1 ORDER BY addtime DESC",
            [$author]
        );
    }

    public static function byFilename($filename)
    {
        return Database::rows(
            "SELECT pid FROM problem WHERE filename=?",
            [$filename]
        );
    }

    public static function byTitle($title)
    {
        return Database::rows(
            "SELECT pid FROM problem WHERE probname=?",
            [$title]
        );
    }

    public static function toggle($id)
    {
        return Database::execute(
            "UPDATE problem SET submitable=1-submitable WHERE pid=?",
            [$id]
        );
    }

    public static function create($title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $created, $author, $difficulty, $checker, $groupId)
    {
        return Database::execute(
            "INSERT INTO problem(probname, filename, readforce, submitable, datacnt, timelimit, memorylimit, detail, addtime, addid, difficulty, plugin, `group`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $created, $author, $difficulty, $checker, $groupId]
        );
    }

    public static function edit($title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $difficulty, $checker, $groupId, $id)
    {
        return Database::execute(
            "UPDATE problem SET probname=?, filename=?, readforce=?, submitable=?, datacnt=?, timelimit=?, memorylimit=?, detail=?, difficulty=?, plugin=?, `group`=?
             WHERE pid=?",
            [$title, $filename, $readLevel, $enabled, $cases, $timeLimit, $memoryLimit, $detail, $difficulty, $checker, $groupId, $id]
        );
    }

    public static function between($first, $last)
    {
        return Database::rows(
            "SELECT * FROM problem WHERE pid BETWEEN ? AND ? ORDER BY pid",
            [$first, $last]
        );
    }

    public static function importTitle($title, $timeLimit, $memoryLimit, $detail, $created, $author, $checker)
    {
        return Database::execute(
            "INSERT INTO problem(probname, readforce, submitable, datacnt, timelimit, memorylimit, detail, addtime, addid, plugin, `group`) VALUES(?,0,0,0, ?, ?, ?, ?, ?, ?,'0')",
            [$title, $timeLimit, $memoryLimit, $detail, $created, $author, $checker]
        );
    }

    public static function setData($cases, $filename, $id)
    {
        return Database::execute(
            "UPDATE problem SET datacnt=?, filename=? WHERE pid=?",
            [$cases, $filename, $id]
        );
    }

    public static function replaceImage($source, $target, $id)
    {
        return Database::execute(
            "UPDATE problem SET detail=replace(detail, ?, ?) WHERE pid=?",
            [$source, $target, $id]
        );
    }

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

    public static function selected(array $ids)
    {
        if (!$ids) return [];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        return Database::rows("SELECT * FROM problem WHERE pid IN ($marks) ORDER BY pid", $ids);
    }

    public static function noteSubmission($problemId, $userId, $accepted)
    {
        Database::execute("UPDATE problem SET submitcnt=submitcnt+1,
            lastacid=IF(?, ?, lastacid), acceptcnt=acceptcnt+IF(? AND NOT EXISTS
            (SELECT 1 FROM submit WHERE pid=? AND uid=? AND accepted=1), 1, 0)
            WHERE pid=?", [$accepted, $userId, $accepted, $problemId, $userId, $problemId]);
    }

    public static function randomUnsolved($userId)
    {
        [$scope, $values] = self::visibility();
        $values[] = (int)$userId;
        return Database::rows("SELECT p.pid FROM problem p WHERE p.submitable=1 AND $scope
            AND NOT EXISTS (SELECT 1 FROM submit s WHERE s.pid=p.pid AND s.uid=? AND s.accepted=1)
            ORDER BY RAND() LIMIT 1", $values)[0]['pid'] ?? null;
    }
}
