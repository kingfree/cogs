<?php
// integration.py 仅在隔离评测服务中用 auto_prepend_file 加载本夹具。web.py 持有排他锁直至重交完成。
$testRoot = getenv('COGS_TEST_ROOT');
if (!$testRoot || !is_file($testRoot.'/contest-grading.gate')) return; // 其他请求和正常安装直接走原评测服务。
require_once dirname(__DIR__).'/include/grader-protocol.php';
if ((array_decode($_POST['query'] ?? '')['action'] ?? '') !== 'grade') return; // 等旧交卷已经通过真实编译才暂停。
$gate = fopen($testRoot.'/contest-grading.gate', 'rb');
touch($testRoot.'/contest-grading.started'); // 通知第二个 Web 进程，现在可以用新版源码覆盖原文件。
flock($gate, LOCK_SH); // web.py 释放排他锁后，评测机继续完成这一点并正常回传旧成绩。
fclose($gate);
