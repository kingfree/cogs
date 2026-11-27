<?php
// 在仓库根目录运行 php tests/grader-comparison.php。比较器必须区分文件读取故障、字节不同和正常 EOF。
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__).'/grader/checker.php';

$root = sys_get_temp_dir().'/cogs-comparison-'.bin2hex(random_bytes(8));
if (!mkdir($root, 0700)) throw new RuntimeException('创建答案比较测试目录失败。');
try {
    file_put_contents($root.'/answer', str_repeat('x', 65535)."\n42\0 ");
    file_put_contents($root.'/output', str_repeat('x', 65535)." 4\t2\0");
    foreach ([1=>1.0, 2=>0.0, 4=>1.0] as $mode=>$expected) {
        $score = checkAnswer('', '', $root.'/answer', $root.'/output', '', $mode);
        if ($score !== $expected) throw new RuntimeException('跨读取块边界的空白或二进制比较结果错误。');
    }
    // 缺少文件会在打开时失败；Linux 目录可以打开，但首次读取就会失败。两边同样失败也不能得满分。
    foreach ([1, 2, 4] as $mode) {
        foreach ([$root.'/missing', $root] as $unreadable) {
            try {
                @checkAnswer('', '', $unreadable, $unreadable, '', $mode);
            } catch (RuntimeException $error) {
                continue;
            }
            throw new RuntimeException('比较器将读取失败当成了有效评分。');
        }
    }
    // 用已存在的普通文件阻挡建目录，再用缺失输入触发复制故障。两种情况都必须在特判启动前中止。
    foreach ([$root.'/answer', $root.'/checker'] as $checker) {
        try {
            @checkAnswer('', $root.'/missing', $root.'/answer', $root.'/output', $checker, 0);
        } catch (RuntimeException $error) {
            continue;
        }
        throw new RuntimeException('评测机在特判工作目录尚未就绪时给出了成绩。');
    }
    echo "三种答案比较、文件读取故障和特判工作目录检查通过\n";
} finally {
    if (is_dir($root.'/checker')) rmdir($root.'/checker');
    unlink($root.'/answer');
    unlink($root.'/output');
    rmdir($root);
}
