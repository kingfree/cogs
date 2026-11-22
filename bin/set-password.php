<?php
// 安装或恢复账号时从主机终端执行 php bin/set-password.php root。
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/include/env.inc.php';
require_once dirname(__DIR__).'/include/db.php';
require_once dirname(__DIR__).'/features/Account.php';
require_once dirname(__DIR__).'/features/Password.php';
$user = Account::byUsername($argv[1] ?? 'root')[0] ?? null;
if (!$user) { fwrite(STDERR, "账号不存在。\n"); exit(1); }
$password = bin2hex(random_bytes(16));
$db = Database::connection();
$db->beginTransaction();
try {
    Account::changePassword(Password::hash($password), $user['uid']);
    Account::recovery('', '', $user['uid']);
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
}
echo $password.PHP_EOL;
