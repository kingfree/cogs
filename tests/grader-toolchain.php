<?php
// 发行版软件包经 /etc/alternatives 选版本。直接用系统路径，防止自定义工具链掩盖漏挂目录。
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__).'/grader/Sandbox.php';

$tools = [
    '/usr/bin/php' => ['-n', '-r', 'echo PHP_VERSION;'],
    '/usr/bin/fpc' => ['-iV'],
];
foreach ($tools as $exe => $arguments) {
    if (!is_link($exe) || !str_starts_with(readlink($exe), '/etc/alternatives/')) continue;
    $result = Sandbox::run(array_merge([$exe], $arguments), getenv('COGS_TEST_ROOT').'/work', 5000, 256);
    if ($result['status'] !== 0 || !preg_match('/\d+\.\d+/', $result['msg'])) {
        throw new RuntimeException($exe.' 在隔离环境内无法读取版本：'.$result['msg']);
    }
    echo $exe.' 通过系统 alternatives 在沙箱中启动：'.trim($result['msg']).PHP_EOL;
}
