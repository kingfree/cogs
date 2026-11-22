<?php
// 语言编号沿用数据库中的 lang，网页、评测协议和编译器使用同一张表。
final class Language
{
    public const TYPES = [
        0 => ['pas', 'Pascal', 'fpc'], 1 => ['c', 'C', 'gcc'],
        2 => ['cpp', 'C++', 'g++'], 3 => ['zip', 'Answer', null],
    ];

    public static function id($extension)
    {
        $id = array_search($extension, array_column(self::TYPES, 0), true);
        return $id === false ? null : $id;
    }

    public static function compiler($id, $source, $output): array
    {
        $tool = self::TYPES[$id][2];
        if ($tool === 'fpc') return [$tool, $source, '-So', '-XS', '-v0', '-O1', '-o'.$output];
        return [$tool, $source, '-O2', '-static', '-o', $output, '-lm'];
    }
}
