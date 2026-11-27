<?php
/** 网页用 Language::id('cpp') 得到数据库编号，评测机用 Language::compiler(2, 'sum.cpp', 'program') 生成编译参数。 */
final class Language
{
    // 数组下标是数据库中的编号，每行依次为提交扩展名、显示名称、编译器和成绩表简写。答案 ZIP 没有编译器。
    public const TYPES = [
        0 => ['pas', 'Pascal', 'fpc', 'P'], 1 => ['c', 'C', 'gcc', 'C'],
        2 => ['cpp', 'C++', 'g++', '+'], 3 => ['zip', 'Answer', null, 'A'],
    ];

    /** 将网页提交的扩展名转换为数据库语言编号；未知扩展名返回 null。 */
    public static function id($extension)
    {
        // 按提交扩展名精确匹配；编号 0 是 Pascal，所以调用方用 null 区分未知语言。
        $id = array_search($extension, array_column(self::TYPES, 0), true);
        return $id === false ? null : $id;
    }

    /** 返回独立参数数组，例 compiler(1, 'sum.c', 'program') 编译 C 源文件。 */
    public static function compiler($id, $source, $output): array
    {
        $tool = self::TYPES[$id][2];
        // FPC 将输出名直接接在 -o 后，C/C++ 将其作为单独参数，并静态链接以便在运行目录启动。
        if ($tool === 'fpc') return [$tool, $source, '-So', '-XS', '-v0', '-O1', '-o'.$output];
        return [$tool, $source, '-O2', '-static', '-o', $output, '-lm'];
    }
}
