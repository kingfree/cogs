<?php
// 复制为 env.inc.php。填写数据库账号，或由 PHP 进程的环境提供密码。
// 例：在仓库根目录运行 cp include/default.env.inc.php include/env.inc.php，再填写本机数据库和目录。
$cfg = [
    'data_server' => 'localhost',
    'data_database' => 'cogs',
    'data_uid' => 'cogs',
    'data_pwd' => getenv('COGS_DB_PASSWORD') ?: '',
    'dir_root' => 'cogs/',
    // 将 /images/ 和 /upload/ 静态映射到此目录下的同名子目录。
    'public_files' => '/var/lib/cogs/public',
    'testdata' => '/home/cogs/testdata',
];
