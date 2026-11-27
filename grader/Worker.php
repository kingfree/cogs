<?php
/**
 * grader/index.php 验证请求后调用 handle()。例如 handle(['action'=>'state']) 查询状态并清理过期任务。
 * lock → compile → grade（每个测试点一次）→ unlock 必须使用同一 job。
 * 当前租约在工作目录中落盘，让不同 PHP 请求和重启后的进程看到同一个占用者。
 */
final class Worker
{
    private const LEASE_SECONDS = 300;

    /** work 用于租约和临时产物，data 是试题目录，name 用于向网页报告机器状态。 */
    public function __construct(private string $work, private string $data, private string $name) {}

    /** 读取部署者指定的三行配置；工作目录或题库目录不存在时抛错，让状态端点返回未就绪。 */
    public static function configured(): self
    {
        // 优先读部署者配置的外部文件，沿用旧部署时才读评测机目录的三行配置。
        $file = getenv('COGS_GRADER_CONFIG') ?: __DIR__.'/settings.php';
        $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
        // 三行依次为机器名、工作目录和题库目录；后两项必须已由部署者建好。
        if (count($lines) < 3 || !$lines[0] || !is_dir($lines[1]) || !is_dir($lines[2])) {
            throw new RuntimeException('评测机目录尚未配置。');
        }
        // 保存两个目录的绝对位置，避免 PHP 服务的启动路径影响题库选择。
        return new self(realpath($lines[1]), realpath($lines[2]), trim($lines[0]));
    }

    /** 释放或重试任务时删除其工作目录；遇到符号链接只移除链接本身。 */
    public static function remove(string $path): void
    {
        // 先检查链接本身，再判断是否为目录，防止清理工作目录时递归到题库或其他任务。
        if (is_link($path) || is_file($path)) { unlink($path); return; }
        if (!is_dir($path)) return; // 任务目录已清理时，重复解锁无需再做操作。
        // 目录只能在子项全部清理后删除；递归入口也会重新检查符号链接。
        foreach (new FilesystemIterator($path) as $item) self::remove($item->getPathname());
        rmdir($path);
    }

    /** 处理已验签并通过字段检查的一次操作；其他任务占用时 lock 返回 busy，陈旧任务号返回 expired。 */
    public function handle(array $request): array
    {
        // 同一台机器只有一个占用者。锁放在工作目录根部，释放任务时不会把锁一并删掉。
        $lock = fopen($this->work.'/.worker.lock', 'c');
        if (!$lock) throw new RuntimeException('无法打开评测机锁文件。');
        try {
            if (!flock($lock, LOCK_EX)) throw new RuntimeException('无法锁定评测机。');
            // 读、执行和写回必须处于同一把锁下，轮询才能看到完整的租约。
            $path = $this->work.'/.worker.json';
            $state = $this->readState($path);
            // 记住进入请求时的状态，读状态的轮询可以跳过写盘，过期清理或续租仍会落盘。
            $previous = $state;
            if ($state['job'] !== null && $state['expires'] < time()) {
                // 网页端断线后不会再发 unlock。回收不增加已完成次数。
                self::remove($this->work.'/'.$state['job']);
                $state['job'] = null;
            }
            // 本次动作和过期回收共用这一把锁。写盘成功后再把结果发回网页。
            $reply = $this->applyRequest($request, $state);
            if ($previous !== $state || !is_file($path)) $this->saveState($path, $state);
            return $reply;
        } finally {
            fclose($lock); // 同时释放独占锁，其他请求才能读新状态或进入工作目录。
        }
    }

    /** 第一次启动从老式 mode/cnt 迁入；已有 JSON 损坏时抛错，避免擅自接管活动任务。 */
    private function readState(string $path): array
    {
        if (is_file($path)) return json_decode(file_get_contents($path), true, 16, JSON_THROW_ON_ERROR);
        return [
            'open' => trim((string)@file_get_contents(__DIR__.'/mode.php')) !== 'closed',
            'count' => (int)@file_get_contents(__DIR__.'/cnt.php'),
            'job' => null, 'expires' => 0,
        ];
    }

    private function saveState(string $path, array $state): void
    {
        // 替换发生在同一文件系统。写入中途退出时，下一次请求仍能读到完整的旧状态。
        $next = $path.'.next';
        if (file_put_contents($next, json_encode($state, JSON_THROW_ON_ERROR)) === false || !rename($next, $path)) {
            throw new RuntimeException('写入评测机状态失败。');
        }
    }

    /** 在调用者持锁期间完成动作；状态和工作目录必须一起更新，不能在编译途中回收目录。 */
    private function applyRequest(array $request, array &$state): array
    {
        $action = $request['action'];
        // 网页选机要区分停机、占用和空闲；停机时即便尚有任务，新的选机也看见 closed。
        if ($action === 'state') {
            $status = $state['job'] === null ? 'free' : 'locked';
            // 即使当前租约尚在执行，停机状态也必须阻止网页继续选这台机器。
            return ['name'=>$this->name, 'ver'=>'2', 'cnt'=>$state['count'], 'state'=>$state['open'] ? $status : 'closed'];
        }
        if ($action === 'start' || $action === 'shutdown') {
            // 停机停止接受新租约。已取得租约的网页仍可完成本轮评分和释放。
            $state['open'] = $action === 'start';
            return ['state'=>'successful'];
        }
        // 以下都是任务操作：协议已经检查 job 的字符和长度，直接以它定位租约目录。
        $job = $request['job'];
        $directory = $this->work.'/'.$job;
        // 同一个 job 重试申请可续租。其他 job 正在运行，或机器停止接单时，返回 busy。
        if ($action === 'lock') {
            if (!$state['open'] || ($state['job'] !== null && $state['job'] !== $job)) return ['state'=>'busy'];
            // 同一任务重试时沿用已有目录，只有第一次成功申请才创建。
            if (!is_dir($directory) && !mkdir($directory, 0700)) throw new RuntimeException('无法创建任务目录。');
            $state['job'] = $job;
        }
        // 只有当前网页请求持有的任务号可以读写其工作目录；到期的旧请求不能继续编译、判题或解锁。
        if ($state['job'] !== $job) return ['state'=>'expired'];
        $state['expires'] = time() + self::LEASE_SECONDS; // 通过持有者检查后才允许续租。
        switch ($action) {
            case 'lock':
                return ['state'=>'successful'];
            // 只有正常解锁增加计数。重试解锁时 job 已清空，不会重复统计。
            case 'unlock':
                self::remove($directory);
                $state['job'] = null;
                $state['count']++; // 只统计完成解锁的任务，过期清理不会走到这里。
                return ['state'=>'successful'];
            // 重新编译必须丢弃上一次的程序，否则编译失败后可能评测旧程序。
            case 'compile':
                $build = $directory.'/build';
                self::remove($build);
                if (!mkdir($build, 0700)) throw new RuntimeException('无法创建编译目录。');
                return compileSubmission($request, $build); // 编译器只在这次新建的目录里写产物。
            // 单点判题重用编译产物；下一个测试点另建运行目录。
            case 'grade':
                return gradeCase($request, $directory, $this->data);
        }
        throw new DomainException('未知评测操作。');
    }
}
