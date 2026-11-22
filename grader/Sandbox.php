<?php
// 编译、运行和特殊判题都在一次性 Linux 命名空间内进行。例 Sandbox::run(['./program'], $dir, 1000, 64)。
final class Sandbox
{
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
        if (is_file('/etc/fpc.cfg')) array_push($box, '--ro-bind', '/etc/fpc.cfg', '/etc/fpc.cfg');
        // 独立安装的编译器可以挂在这里。路径只来自评测机部署配置。
        $toolchain = getenv('COGS_GRADER_TOOLCHAIN');
        if ($toolchain) array_push($box, '--ro-bind', realpath($toolchain), '/opt/toolchain');
        $seconds = max(1, (int)ceil($milliseconds / 1000));
        array_push($box, '--', 'prlimit', '--as='.($memoryMb * 1048576), '--cpu='.$seconds,
            '--fsize=16777216', '--nofile=64', '--nproc=64', '--core=0', '--', ...$command);
        $deadline = number_format($milliseconds / 1000, 3, '.', '').'s';
        return array_merge(['timeout', '-k', '0.2s', $deadline], $box);
    }

    public static function run(array $command, string $directory, int $milliseconds, int $memoryMb): array
    {
        $metrics = tempnam(sys_get_temp_dir(), 'cogs-time-');
        try {
            $cmd = array_merge(['time', '-f', '%M', '-o', $metrics], self::command($command, $directory, $milliseconds, $memoryMb));
            $start = hrtime(true);
            $process = proc_open($cmd, [0=>['file','/dev/null','r'], 1=>['pipe','w'], 2=>['redirect',1]], $pipes,
                null, ['PATH'=>getenv('PATH') ?: '/usr/bin:/bin']);
            if (!is_resource($process)) throw new RuntimeException('无法启动隔离环境。');
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
