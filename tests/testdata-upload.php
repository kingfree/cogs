<?php
// 运行 php tests/testdata-upload.php，检查 ZIP 正常安装、目标冲突、题目提交失败时的磁盘内容。
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/features/Archive.php';
require dirname(__DIR__).'/features/TestData.php';

$root = sys_get_temp_dir().'/cogs-testdata-'.bin2hex(random_bytes(12));
if (!mkdir($root, 0700)) throw new RuntimeException('无法创建独立的测试目录。');
$cfg = ['testdata'=>$root];  // 本次运行只操作刚创建的目录，两个并行测试互不影响。
try {
    TestData::store('sum', 'sum1.in', '原有输入');
    $input = $root.'/sum/sum1.in';
    $answer = $root.'/sum/sum1.ans';  // 一个旧点被覆盖，一个新答案被加入，回退时应分别处理。
    $package = $root.'/data.zip';
    $zip = new ZipArchive();
    if ($zip->open($package, ZipArchive::CREATE) !== true ||
        !$zip->addFromString('sum1.in', '新版输入') || !$zip->addFromString('sum1.ans', '新版答案') || !$zip->close()) {
        throw new RuntimeException('无法生成测试 ZIP。');
    }

    // 第二个成员目标是目录时，第一个已有输入也必须保持原文。
    if (!mkdir($answer)) throw new RuntimeException('无法准备目标冲突。');
    try {
        TestData::importZip('sum', 1, $package, false);
        throw new RuntimeException('目标冲突时导入应该失败。');
    } catch (DomainException $error) {
        if (!is_dir($answer) || file_get_contents($input) !== '原有输入') throw new RuntimeException('冲突时旧数据被改变。');
    }
    rmdir($answer);

    // 提交回调确认两个新文件均可读取后，模拟数据库在提交题目时拒绝本次更新。
    $declined = new RuntimeException('模拟数据库提交失败。');
    try {
        TestData::importZip('sum', 1, $package, false, function () use ($input, $answer, $declined) {
            if (file_get_contents($input) !== '新版输入' || file_get_contents($answer) !== '新版答案') {
                throw new RuntimeException('在文件就位前调用了题目提交。');
            }
            throw $declined;
        });
        throw new RuntimeException('题目提交错误没有传回调用者。');
    } catch (RuntimeException $error) {
        if ($error !== $declined) throw $error;
    }
    if (file_get_contents($input) !== '原有输入' || file_exists($answer)) throw new RuntimeException('提交失败后测试点未恢复。');

    // 回退之后，用同一题包重试。检查内容和目录清单，暂存文件不能影响下一次上传。
    TestData::importZip('sum', 1, $package, false);
    if (file_get_contents($input) !== '新版输入' || file_get_contents($answer) !== '新版答案' ||
        scandir($root.'/sum') !== ['.', '..', 'sum1.ans', 'sum1.in']) {
        throw new RuntimeException('重试后数据或目录清单不符。');
    }
} finally {
    // 即便断言在中途失败，也从子项向上删除本次运行的全部临时文件和目录。
    $entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($entries as $entry) {
        if ($entry->isDir() && !$entry->isLink()) rmdir($entry->getPathname());
        else unlink($entry->getPathname());
    }
    rmdir($root);
}
echo "测试数据 ZIP 正常安装、目标冲突和题目提交失败回退检查通过\n";
