<?php
// 一个评测任务独占一台评测机。文件锁串行化租用、编译、评分和释放，超时后可以回收任务。
final class Worker
{
    public function __construct(private string $work, private string $data, private string $name) {}

    public static function configured(): self
    {
        $file = getenv('COGS_GRADER_CONFIG') ?: __DIR__.'/settings.php';
        $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
        if (count($lines) < 3 || !$lines[0] || !is_dir($lines[1]) || !is_dir($lines[2])) {
            throw new RuntimeException('评测机目录尚未配置。');
        }
        return new self(realpath($lines[1]), realpath($lines[2]), trim($lines[0]));
    }

    public static function remove(string $path): void
    {
        if (is_link($path) || is_file($path)) { unlink($path); return; }
        if (!is_dir($path)) return;
        foreach (new FilesystemIterator($path) as $item) self::remove($item->getPathname());
        rmdir($path);
    }

    public function handle(array $request): array
    {
        $lock = fopen($this->work.'/.worker.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX)) throw new RuntimeException('无法锁定评测机。');
        try {
            $path = $this->work.'/.worker.json';
            $state = is_file($path) ? json_decode(file_get_contents($path), true, 16, JSON_THROW_ON_ERROR)
                : ['open'=>trim((string)@file_get_contents(__DIR__.'/mode.php')) !== 'closed',
                   'count'=>(int)@file_get_contents(__DIR__.'/cnt.php'), 'job'=>null, 'expires'=>0];
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
