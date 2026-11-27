<?php
// 安装或恢复账号时从主机终端执行 php bin/set-password.php root。
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/include/env.inc.php'; // 读取当前站点的数据库配置。
require_once dirname(__DIR__).'/utils/database.php'; // 账号变更使用同一个事务。
require_once dirname(__DIR__).'/features/Account.php'; // 读取账号并更新凭据。
require_once dirname(__DIR__).'/features/Password.php'; // 使用站点统一的密码哈希。
$user = Account::byUsername($argv[1] ?? 'root')[0] ?? null; // 终端缺省操作 root，也可显式指定已有账号。
if (!$user) { fwrite(STDERR, "账号不存在。\n"); exit(1); }
$password = bin2hex(random_bytes(16)); // 生成 128 位临时密码，数据库只存哈希。
$db = Database::connection();
$db->beginTransaction(); // 重置密码与清空旧的找回问题必须同时生效。
try {
    Account::changePassword(Password::hash($password), $user['uid']);
    Account::recovery('', '', $user['uid']); // 收回旧找回答案，避免凭旧答案再次登录。
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack(); // 任一写入失败便保留原凭据，不打印临时密码。
    throw $error;
}
echo $password.PHP_EOL; // 事务提交后仅在当前终端显示一次。
