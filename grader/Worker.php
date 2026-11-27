<?php
/**
 * grader/index.php 验证请求后调用 handle()。例如 handle(['action'=>'state']) 只读取机器状态。
 * lock → compile → grade（每个测试点一次）→ unlock 必须使用同一 job。
 * 当前租约在工作目录中落盘，让不同 PHP 请求和重启后的进程看到同一个占用者。
 */
final class Worker
{
    /** work 用于租约和临时产物，data 是试题目录，name 用于向网页报告机器状态。 */
    public function __construct(private string $work, private string $data, private string $name) {}

    /** 读取部署者指定的三行配置；工作目录或题库目录不存在时抛错，让状态端点返回未就绪。 */
    public static function configured(): self
    {
        $file = getenv('COGS_GRADER_CONFIG') ?: __DIR__.'/settings.php';
        $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
        if (count($lines) < 3 || !$lines[0] || !is_dir($lines[1]) || !is_dir($lines[2])) {
            throw new RuntimeException('评测机目录尚未配置。');
        }
        return new self(realpath($lines[1]), realpath($lines[2]), trim($lines[0]));
    }

    /** 释放或重试任务时删除其工作目录；遇到符号链接只移除链接本身。 */
    public static function remove(string $path): void
    {
        if (is_link($path) || is_file($path)) { unlink($path); return; }
        if (!is_dir($path)) return;
        foreach (new FilesystemIterator($path) as $item) self::remove($item->getPathname());
        rmdir($path);
    }

    /** 处理已验签并通过字段检查的一次操作；其他任务占用时 lock 返回 busy，陈旧任务号返回 expired。 */
    public function handle(array $request): array
    {
        $lock = fopen($this->work.'/.worker.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX)) throw new RuntimeException('无法锁定评测机。');
        try {
            $path = $this->work.'/.worker.json';
            $state = is_file($path) ? json_decode(file_get_contents($path), true, 16, JSON_THROW_ON_ERROR)
                : ['open'=>trim((string)@file_get_contents(__DIR__.'/mode.php')) !== 'closed',
                   'count'=>(int)@file_get_contents(__DIR__.'/cnt.php'), 'job'=>null, 'expires'=>0];
            // 网页端有可能中途断线。租约过期后由下一次请求清理旧目录并接手。
            if ($state['job'] && $state['expires'] < time()) {
                self::remove($this->work.'/'.$state['job']);
                $state['job'] = null;
            }
            $action = $request['action'];
            $job = $request['job'] ?? null;
            $reply = ['state'=>'successful'];
            if ($action === 'state') {
                $reply = ['name'=>$this->name, 'ver'=>'2', 'cnt'=>$state['count'],
                    'state'=>!$state['open'] ? 'closed' : ($state['job'] ? 'locked' : 'free')];
            } elseif ($action === 'start' || $action === 'shutdown') {
                $state['open'] = $action === 'start';
            } elseif ($action === 'lock') {
                if (!$state['open'] || ($state['job'] && $state['job'] !== $job)) return ['state'=>'busy'];
                $state['job'] = $job;
                $state['expires'] = time() + 300;
                if (!is_dir($this->work.'/'.$job)) mkdir($this->work.'/'.$job, 0700);
            } elseif ($state['job'] !== $job) {
                return ['state'=>'expired'];
            } else {
                $dir = $this->work.'/'.$job;
                $state['expires'] = time() + 300;
                if ($action === 'unlock') {
                    self::remove($dir);
                    $state['job'] = null;
                    $state['count']++;
                } elseif ($action === 'compile') {
                    self::remove($dir.'/build');
                    mkdir($dir.'/build', 0700);
                    $reply = compileSubmission($request, $dir.'/build');
                } elseif ($action === 'grade') {
                    $reply = gradeCase($request, $dir, $this->data);
                }
            }
            // 持锁写入临时文件再 rename；即使写到一半退出，旧状态仍是一份完整 JSON。
            $next = $path.'.next';
            if (file_put_contents($next, json_encode($state, JSON_THROW_ON_ERROR)) === false || !rename($next, $path)) {
                throw new RuntimeException('写入评测机状态失败。');
            }
            return $reply;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
