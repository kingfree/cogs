<?php
// 复制为 env.inc.php。填写数据库账号，或由 PHP 进程的环境提供密码。
$cfg = [
    'data_server' => 'localhost',
    'data_database' => 'cogs',
    'data_uid' => 'cogs',
    'data_pwd' => getenv('COGS_DB_PASSWORD') ?: '',
    'dir_root' => 'cogs/',
    'testdata' => '/home/cogs/testdata',
];
