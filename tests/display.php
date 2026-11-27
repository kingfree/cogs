<?php
/** 执行 php tests/display.php 验证子目录部署、源码语言选择、判定说明、分页与公开数据盘的约定。 */
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/utils/forms.php';
require dirname(__DIR__).'/utils/paths.php';
require dirname(__DIR__).'/utils/pagination.php';
require dirname(__DIR__).'/include/results.php';
require dirname(__DIR__).'/grader/Language.php';

$SET = ['cur'=>'/srv/www/oj/contest/report.php', 'base'=>'/srv/www/oj/', 'global_root'=>'oj/', 'style_pagesize'=>10];
if (路径('problem/index.php') !== '../problem/index.php') throw new RuntimeException('子目录内链接的相对位置错误');
foreach (['/srv/cogs/public', 'C:/cogs/public', 'C:\\cogs\\public'] as $path) {
    $cfg = ['public_files'=>$path];
    if (publicFilePath('upload') !== $path.'/upload') throw new RuntimeException('公开目录错误');
}
$cfg = [];
if (publicFilePath('images') !== dirname(__DIR__).'/images') throw new RuntimeException('旧配置图片位置错误');
try {
    $cfg['public_files'] = 'relative/data';
    publicFilePath('upload');
    throw new LogicException('相对配置会让启动目录影响数据位置');
} catch (RuntimeException $expected) {
    if (!str_contains($expected->getMessage(), '绝对路径')) throw $expected;
}

foreach ([false, true] as $withIds) {
    ob_start();
    renderLanguageChoices($withIds);
    $choices = ob_get_clean();
    foreach (Language::TYPES as [$extension, $name, $compiler]) {
        if (str_contains($choices, 'value="'.$extension.'"') !== ($compiler !== null)) throw new RuntimeException('题面语言选项与编译器不一致');
        if (str_contains($choices, 'id="'.$extension.'"') !== ($compiler !== null && $withIds)) throw new RuntimeException('快捷键定位到错误表单');
    }
}
if (评测信息a('PW') !== "<span class='jP'>答案部分正确</span><br /><span class='jW'>答案错误</span><br />") throw new RuntimeException('测试点中文含义或次序异常');
if (检测页面(21, 99, 10) !== 20 || 检测页面(21, -1, 10) !== 0) throw new RuntimeException('倒序或越界页码的查询偏移错误');
foreach ([[-1, 1], [99, 3]] as [$requested, $shown]) {
    ob_start();
    分页(21, $requested, '?', 10);
    $pages = ob_get_clean();
    if (!str_contains($pages, 'class="active"><a>'.$shown.'</a>') || preg_match('/page=(0|4)"/', $pages)) throw new RuntimeException('导航指向不存在的页码');
}
echo "子目录、数据路径、语言选择、逐点结果及分页边界通过\n";
