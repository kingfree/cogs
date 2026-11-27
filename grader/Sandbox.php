<?php
/**
 * 编译器、选手程序和判题器均经此处启动。每次调用只把指定的目录挂到 /work。
 * 例如 Sandbox::run(['./program'], $caseDir, 1000, 64) 限时一秒、限制地址空间 64 MiB。
 * 调用方从 status 读退出码，从 rtime、memory 读实际耗时（毫秒）和峰值内存（KiB）。
 */
final class Sandbox
{
    /** 组装无网络、受限资源的子进程参数；调用者提供本次任务唯一可写的宿主目录。 */
    private static function command(array $command, string $directory, int $milliseconds, int $memoryMb): array
    {
        $box = ['unshare', '--user', '--map-root-user', '--net', '--', 'bwrap', '--die-with-parent',
            '--new-session', '--unshare-pid', '--unshare-ipc', '--unshare-uts', '--cap-drop', 'ALL',
            '--dir', '/usr', '--symlink', 'usr/bin', '/bin', '--symlink', 'usr/lib', '/lib',
            '--dir', '/etc', '--dev', '/dev', '--proc', '/proc', '--tmpfs', '/tmp',
            '--bind', $directory, '/work', '--chdir', '/work', '--clearenv',
            '--setenv', 'PATH', '/opt/toolchain/bin:/usr/bin', '--setenv', 'HOME', '/tmp',
            '--setenv', 'LANG', 'C'];
        foreach (['bin','lib','lib64','libexec','include','share/fpcsrc','share/zoneinfo'] as $part) {
            if (is_dir('/usr/'.$part)) array_push($box, '--ro-bind', '/usr/'.$part, '/usr/'.$part);
        }
        if (is_dir('/usr/lib64')) array_push($box, '--symlink', 'usr/lib64', '/lib64');
        // Debian/Ubuntu 的 /usr/bin/php、fpc 会经过此目录选择实际版本。
        // 只读挂载保留宿主机已选择的工具链；沙箱里的 /etc 仍只含显式挂载的内容。
        if (is_dir('/etc/alternatives')) array_push($box, '--ro-bind', '/etc/alternatives', '/etc/alternatives');
        if (is_file('/etc/fpc.cfg')) array_push($box, '--ro-bind', '/etc/fpc.cfg', '/etc/fpc.cfg');
        // 独立安装的编译器可以挂在这里。路径只来自评测机部署配置。
        $toolchain = getenv('COGS_GRADER_TOOLCHAIN');
        if ($toolchain) array_push($box, '--ro-bind', realpath($toolchain), '/opt/toolchain');
        $seconds = max(1, (int)ceil($milliseconds / 1000));
        array_push($box, '--', 'prlimit', '--as='.($memoryMb * 1048576), '--cpu='.$seconds,
            '--fsize=16777216', '--nofile=64', '--nproc=64', '--core=0', '--', ...$command);
        // CPU 限额不能终止一直等待输入的程序；timeout 另按墙钟时间收尾。
        $deadline = number_format($milliseconds / 1000, 3, '.', '').'s';
        return array_merge(['timeout', '-k', '0.2s', $deadline], $box);
    }

    /** 执行一次编译或单向程序，返回退出状态、限时标志、诊断、耗时与峰值内存。 */
    public static function run(array $command, string $directory, int $milliseconds, int $memoryMb): array
    {
        $metrics = tempnam(sys_get_temp_dir(), 'cogs-time-');
        try {
            $cmd = array_merge(['time', '-f', '%M', '-o', $metrics], self::command($command, $directory, $milliseconds, $memoryMb));
            $start = hrtime(true);
            $process = proc_open($cmd, [0=>['file','/dev/null','r'], 1=>['pipe','w'], 2=>['redirect',1]], $pipes,
                null, ['PATH'=>getenv('PATH') ?: '/usr/bin:/bin']);
            if (!is_resource($process)) throw new RuntimeException('无法启动隔离环境。');
            // 即使诊断信息已满，仍要读到 EOF，避免程序卡在写满的管道上。
            $message = '';
            while (!feof($pipes[1])) {
                $chunk = fread($pipes[1], 8192);
                if ($chunk === false) break;
                if (strlen($message) < 65536) $message .= substr($chunk, 0, 65536 - strlen($message));
            }
            fclose($pipes[1]);
            $status = proc_close($process);
            $rss = trim(file_get_contents($metrics));
            return ['status'=>$status, 'msg'=>$message, 'rtime'=>(hrtime(true)-$start)/1000000,
                'memory'=>(int)array_slice(explode("\n", $rss), -1)[0], 'timeout'=>in_array($status, [124,137,152], true)];
        } finally {
            unlink($metrics);
        }
    }

    /** 用双向管道连接两个独立目录中的程序。选手正常退出后，交互器的退出码 0～100 表示得分百分比。 */
    public static function interact(string $solution, string $checker, int $milliseconds, int $memory): array
    {
        // 两个目录分开挂载，双方仅通过标准输入输出交换数据。
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
        if (!$pair) throw new RuntimeException('不能创建交互通道。');
        $metrics = tempnam(sys_get_temp_dir(), 'cogs-time-');
        $processes = [];
        $env = ['PATH'=>getenv('PATH') ?: '/usr/bin:/bin'];
        $start = hrtime(true);
        try {
            $judge = self::command(['./interactor','input','answer'], $checker, $milliseconds + 1500, 256);
            $run = self::command(['./program'], $solution, $milliseconds, $memory);
            $processes[] = proc_open($judge, [0=>$pair[0],1=>$pair[0],2=>['file','/dev/null','w']], $pipes, null, $env);
            $processes[] = proc_open(array_merge(['time','-f','%M','-o',$metrics], $run),
                [0=>$pair[1],1=>$pair[1],2=>['file','/dev/null','w']], $pipes, null, $env);
            // 子进程都继承句柄后，父进程必须关闭两端，任一方退出时对端才能读到 EOF。
            foreach ($pair as $stream) fclose($stream);
            if (!is_resource($processes[0]) || !is_resource($processes[1])) throw new RuntimeException('不能启动交互评测。');
            $status = proc_close($processes[1]);
            $elapsed = (hrtime(true)-$start)/1000000;
            $score = proc_close($processes[0]);
            $usage = explode("\n", trim(file_get_contents($metrics)));
            return ['exitcode'=>$status, 'runerr'=>(int)($status !== 0), 'rtime'=>$elapsed,
                'timeout'=>(int)in_array($status, [124,137,152], true), 'memory'=>(int)end($usage),
                'score'=>$status === 0 && $score <= 100 && $score >= 0 ? $score/100 : 0];
        } finally {
            foreach ($pair as $stream) if (is_resource($stream)) fclose($stream);
            foreach ($processes as $proc) if (is_resource($proc)) { proc_terminate($proc, 9); proc_close($proc); }
            unlink($metrics);
        }
    }
}
