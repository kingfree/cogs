<?php
/**
 * php tests/profile-background.php 测试资料与已转码背景的发布。故意让数据库在 commit 处失败，覆盖旧图已被替换的窗口。
 * 真实的上传、PNG 解码与公开目录映射由 HTTP 集成测试覆盖。
 */
if (PHP_SAPI !== 'cli') exit;
require_once __DIR__.'/../features/Account.php';

/** 测试中的事务只存资料昵称；可在重名检查或提交时失败，观察图片与资料是否一起恢复。 */
final class Database
{
    public static string $failure = '';
    public static string $nickname = '上次资料';
    private static ?string $pending = null;

    public static function connection(): self { return new self; }
    public function beginTransaction(): void { self::$pending = self::$nickname; }
    public function inTransaction(): bool { return self::$pending !== null; }
    public function rollBack(): void { self::$pending = null; }

    public static function execute(string $sql, array $values): void
    {
        if (self::$failure === 'duplicate') {
            $error = new PDOException('模拟登录名唯一索引冲突');
            $error->errorInfo = ['23000', 1062, 'duplicate'];
            throw $error;
        }
        self::$pending = $values[1];
    }

    public function commit(): void
    {
        if (self::$failure === 'commit') throw new PDOException('模拟数据库拒绝提交');
        self::$nickname = self::$pending;
        self::$pending = null;
    }
}

$directory = sys_get_temp_dir().'/cogs-profile-'.bin2hex(random_bytes(8));
mkdir($directory, 0700);
$destination = "$directory/3.png";
$staged = "$directory/encoded.png"; // 这个临时文件模拟上传入口已经完成的 PNG 转码。
$profile = ['usr'=>'student', 'nickname'=>'新资料', 'realname'=>'', 'email'=>'a@example.org', 'memo'=>''];

// 故障注入在发布步骤验证，不需要在生产代码中添加可控制的失败选项。
$save = new ReflectionMethod(Account::class, 'saveProfile');
try {
    foreach (['commit', 'duplicate', ''] as $failure) {
        foreach ([false, true] as $hadImage) {
            foreach ([false, true] as $withUpload) {
                if (is_file($destination)) unlink($destination);
                if ($hadImage) file_put_contents($destination, '旧图');
                if ($withUpload) file_put_contents($staged, '新图');
                Database::$failure = $failure;
                Database::$nickname = '上次资料';

                try {
                    $save->invoke(null, 3, $profile, '', false, $withUpload ? $staged : null);
                    if ($failure !== '') throw new RuntimeException('未传播注入的失败');
                } catch (PDOException | InvalidArgumentException $error) {
                    if ($failure === 'duplicate' && !($error instanceof InvalidArgumentException)) throw $error;
                    if ($failure === 'commit' && !($error instanceof PDOException)) throw $error;
                    if ($failure === '') throw $error;
                }
                $expectedImage = $withUpload && $failure === '' ? '新图' : ($hadImage ? '旧图' : null);
                $actualImage = is_file($destination) ? file_get_contents($destination) : null;
                $expectedName = $failure === '' ? '新资料' : '上次资料';
                if ($actualImage !== $expectedImage || Database::$nickname !== $expectedName ||
                    Database::connection()->inTransaction() || glob("$directory/.previous-*")) {
                    throw new RuntimeException('资料、图片或备份状态错误 '.json_encode([$failure, $hadImage, $withUpload]));
                }
                if (is_file($staged)) unlink($staged); // 调用入口的 finally 负责尚未发布的临时图。
            }
        }
    }
    echo "个人资料与背景图片的 12 个提交、回滚及重复用户名分支通过\n";
} finally {
    foreach (new FilesystemIterator($directory) as $file) unlink($file->getPathname());
    rmdir($directory);
}
