<?php
// 语言编号沿用数据库中的 lang，网页、评测协议和编译器使用同一张表。
final class Language
{
    public const TYPES = [
        0 => ['pas', 'Pascal', 'fpc'], 1 => ['c', 'C', 'gcc'],
        2 => ['cpp', 'C++', 'g++'], 3 => ['zip', 'Answer', null],
    ];

    /** 将网页提交的扩展名转换为数据库语言编号；未知扩展名返回 null。 */
    public static function id($extension)
    {
        $id = array_search($extension, array_column(self::TYPES, 0), true);
        return $id === false ? null : $id;
    }

    // 返回独立 argv，不拼 shell 命令。例 compiler(1, 'sum.c', 'program') 编译 C 源文件。
    public static function compiler($id, $source, $output): array
    {
        $tool = self::TYPES[$id][2];
        if ($tool === 'fpc') return [$tool, $source, '-So', '-XS', '-v0', '-O1', '-o'.$output];
        return [$tool, $source, '-O2', '-static', '-o', $output, '-lm'];
    }
}
