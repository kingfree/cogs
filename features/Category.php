<?php
// 题目分类与标签。例如 Category::directory()。
final class Category
{
    public static function directory()
    {
        return Database::rows(
            "SELECT caid, cname FROM category ORDER BY cname ASC"
        );
    }

    public static function all()
    {
        return Database::rows(
            "SELECT * FROM category ORDER BY cname"
        );
    }

    public static function matching($name, $memo)
    {
        return Database::rows(
            "SELECT * FROM category WHERE cname LIKE ? OR memo LIKE ? LIMIT 1",
            [$name, $memo]
        );
    }

    public static function create($name, $memo)
    {
        return Database::execute(
            "INSERT INTO category(cname, memo) VALUES(?, ?)",
            [$name, $memo]
        );
    }

    public static function updateMemo($memo, $id)
    {
        return Database::execute(
            "UPDATE category SET memo=? WHERE caid=?",
            [$memo, $id]
        );
    }

    public static function hasProblem($problemId, $categoryId)
    {
        return Database::rows(
            "SELECT * FROM tag WHERE pid=? AND caid=?",
            [$problemId, $categoryId]
        );
    }

    public static function attach($problemId, $categoryId)
    {
        return Database::execute(
            "INSERT INTO tag(pid, caid) VALUES(?, ?)",
            [$problemId, $categoryId]
        );
    }

    public static function edit($name, $memo, $id)
    {
        return Database::execute(
            "UPDATE category SET cname=?, memo=? WHERE caid=?",
            [$name, $memo, $id]
        );
    }

    public static function detach($problemId, $categoryId)
    {
        return Database::execute(
            "DELETE FROM tag WHERE pid=? AND caid=?",
            [$problemId, $categoryId]
        );
    }

    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM category WHERE caid=?",
            [$id]
        );
    }

    public static function forProblem($problemId)
    {
        return Database::rows(
            "SELECT caid FROM tag WHERE pid=?",
            [$problemId]
        );
    }

    public static function labels($problemId)
    {
        return Database::rows(
            "SELECT category.cname, category.memo, category.caid
             FROM category, tag
             WHERE tag.pid=? AND category.caid=tag.caid",
            [$problemId]
        );
    }

    public static function setForProblem($id, array $categories): void
    {
        $before = array_column(self::forProblem($id), 'caid');
        foreach (array_diff($before, $categories) as $category) self::detach($id, $category);
        foreach (array_diff($categories, $before) as $category) self::attach($id, $category);
    }

}
