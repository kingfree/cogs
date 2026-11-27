<?php
/**
 * 管理分类名称、说明，以及每道题勾选的分类。category 保存原文，tag 只保存题号和分类编号。
 * 页面可以调用 $labels = Category::labels(1)，再用 h($label['cname']) 显示单个名称。
 * 名称既用于检索，也出现在 HTML 正文和属性里，写入数据库时保留原文。
 */
final class Category
{
    /** 站点地图只需要分类编号和名称，按名称排列。 */
    public static function directory()
    {
        return Database::rows(
            "SELECT caid, cname FROM category ORDER BY cname ASC"
        );
    }

    /** 管理页同时列出名称和说明，供编辑题目时勾选。 */
    public static function all()
    {
        return Database::rows(
            "SELECT * FROM category ORDER BY cname"
        );
    }

    /**
     * 在题面添加标签。复用匹配名称或说明的已有分类，返回 [分类编号, 原本是否已有这个标签]。
     * 例：[$categoryId, $alreadyTagged] = Category::labelProblem(1, '动态规划', '区间问题');
     */
    public static function labelProblem(int $problem, string $name, string $memo): array
    {
        $db = Database::connection();
        // 说明及其标签关联属于一次提交，要一起成功。
        $db->beginTransaction();
        try {
            // 同一题上的两个表单按顺序更新关联；数据库中的历史 tag 表没有组合唯一键。
            Database::rows("SELECT pid FROM problem WHERE pid=? FOR UPDATE", [$problem]);
            // 沿用题面已有的模糊匹配约定，同时检索名称和说明。
            $pattern = '%'.$name.'%';
            $match = self::matching($pattern, $pattern)[0] ?? null;
            if ($match) {
                // 匹配说明也可能命中旧名称，复用实际命中的编号。
                $categoryId = (int)$match['caid'];
                // 不同题目可能同时给同一分类追加说明，读取和拼接交给数据库一次完成。
                Database::execute("UPDATE category SET memo=TRIM(CONCAT(COALESCE(memo, ''),' ',?)) WHERE caid=?", [$memo, $categoryId]);
                // 题目可能已贴过旧分类；这次仍保留用户补充的说明。
                $alreadyTagged = (bool)self::hasProblem($problem, $categoryId);
            } else {
                // 两道题同时创建同名分类时，唯一键留下一个编号，各自的说明均追加到该分类。
                Database::execute("INSERT INTO category(cname, memo) VALUES(?, ?)
                    ON DUPLICATE KEY UPDATE caid=LAST_INSERT_ID(caid), memo=TRIM(CONCAT(COALESCE(memo,''),' ',?))",
                    [$name, $memo, $memo]);
                // 插入或命中唯一键都返回同一个分类编号。
                $categoryId = (int)$db->lastInsertId();
                $alreadyTagged = false;
            }
            if (!$alreadyTagged) self::attach($problem, $categoryId);
            // 题目关联创建完毕，再向页面返回分类号与重复提示。
            $db->commit();
            return [$categoryId, $alreadyTagged];
        } catch (Throwable $error) {
            // 不能在关联写入失败后留下孤立的新分类或部分说明。
            $db->rollBack();
            throw $error;
        }
    }

    /** 按名称或说明模糊匹配一个已有分类。两个参数是带 % 的 LIKE 模式。 */
    public static function matching($name, $memo)
    {
        return Database::rows(
            "SELECT * FROM category WHERE cname LIKE ? OR memo LIKE ? LIMIT 1",
            [$name, $memo]
        );
    }

    /** 新建分类并返回它的编号。可将编号直接传给 attach，避免再次模糊匹配其他分类。 */
    public static function create($name, $memo): int
    {
        Database::execute(
            "INSERT INTO category(cname, memo) VALUES(?, ?)",
            [$name, $memo]
        );
        return (int)Database::connection()->lastInsertId();   // 使用这次 INSERT 成功写入的编号。
    }

    /** 查题目和分类的现有关联。空数组表示还没有贴过此标签。 */
    public static function hasProblem($problemId, $categoryId)
    {
        return Database::rows(
            "SELECT * FROM tag WHERE pid=? AND caid=?",
            [$problemId, $categoryId]
        );
    }

    /** 为题目记录一个分类，两个编号分别来自 problem 和 category。 */
    public static function attach($problemId, $categoryId)
    {
        return Database::execute(
            "INSERT INTO tag(pid, caid) VALUES(?, ?)",
            [$problemId, $categoryId]
        );
    }

    /** 按编号修改整个站点共用的分类，所有使用它的题目随之显示新文字。 */
    public static function edit($name, $memo, $id)
    {
        return Database::execute(
            "UPDATE category SET cname=?, memo=? WHERE caid=?",
            [$name, $memo, $id]
        );
    }

    /** 解除这一道题与分类的关联，其他题目仍可使用该分类。 */
    public static function detach($problemId, $categoryId)
    {
        return Database::execute(
            "DELETE FROM tag WHERE pid=? AND caid=?",
            [$problemId, $categoryId]
        );
    }

    /** 按分类编号读取编辑表单的初始值。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM category WHERE caid=?",
            [$id]
        );
    }

    /** 返回已勾选的分类编号，供题目编辑表单和差量保存使用。 */
    public static function forProblem($problemId)
    {
        return Database::rows(
            "SELECT caid FROM tag WHERE pid=?",
            [$problemId]
        );
    }

    /** 返回一题的分类名称、说明和编号。调用方按显示位置转义文本。 */
    public static function labels($problemId)
    {
        return Database::rows(
            // 通过 tag 连接题号和分类，展示时取得这一分类当前的名称和说明。
            "SELECT category.cname, category.memo, category.caid
             FROM category, tag
             WHERE tag.pid=? AND category.caid=tag.caid",
            [$problemId]
        );
    }

    /** 将题目的分类同步为表单勾选的编号，只添加和移除发生变化的关联。 */
    public static function setForProblem($id, array $categories): void
    {
        $before = array_column(self::forProblem($id), 'caid');   // 表单没勾的旧标签将被移除。
        foreach (array_diff($before, $categories) as $category) self::detach($id, $category);
        foreach (array_diff($categories, $before) as $category) self::attach($id, $category);
    }

}
