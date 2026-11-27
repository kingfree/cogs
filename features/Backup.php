<?php
/**
 * 管理页把数据库、选手代码、比赛代码和测试数据分别备份到站点指定的目录。
 * 例：$filename = Backup::create('usercode', 'weekly', $SET, $cfg);
 */
final class Backup
{
    /** 按类型选择受配置的来源，成功返回备份文件名；数据库导出或压缩失败时抛出异常。 */
    public static function create($type, $label, array $settings, array $config): string
    {
        $sources = ['usercode'=>$settings['dir_source'], 'compcode'=>$settings['dir_competition'], 'testdata'=>$config['testdata']];
        if (!is_string($label) || !preg_match('/^[A-Za-z0-9_][A-Za-z0-9_.-]{0,99}$/D', $label) ||
            ($type !== 'database' && !isset($sources[$type]))) throw new DomainException('备份名称或种类无效。');
        $dest = rtrim($settings['dir_databackup'], '/').'/'.$type;
        if (!is_dir($dest) && !mkdir($dest, 0700, true) && !is_dir($dest)) throw new RuntimeException('不能创建备份目录。');
        $name = $label.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
        $tmp = tempnam($dest, '.backup-');
        try {
            if ($type === 'database') {
                $host = explode(';port=', $config['data_server'], 2);
                $cmd = ['mariadb-dump','--no-defaults','--single-transaction','--host='.$host[0],
                    '--user='.$config['data_uid'], '--port='.($host[1] ?? 3306), '--databases', $config['data_database']];
                // 将导出工具的标准输出交给 gzip；密码通过子进程环境传入。
                $dump = proc_open($cmd, [0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['file','/dev/null','w']],
                    $pipes, null, array_merge(getenv(), ['MYSQL_PWD'=>$config['data_pwd']]));
                if (!is_resource($dump)) throw new RuntimeException('不能启动数据库备份。');
                $gzip = gzopen($tmp, 'wb');
                $ok = $gzip !== false;
                while ($ok && !feof($pipes[1])) {
                    $chunk = fread($pipes[1], 65536);
                    $ok = $chunk !== false && gzwrite($gzip, $chunk) === strlen($chunk);
                }
                fclose($pipes[1]);
                if ($gzip) $ok = gzclose($gzip) && $ok;
                if (proc_close($dump) !== 0 || !$ok) throw new RuntimeException('数据库备份失败。');
                $name .= '.sql.gz';
            } else {
                $process = proc_open(['tar','-czf',$tmp,'-C',$sources[$type],'.'],
                    [0=>['file','/dev/null','r'],1=>['file','/dev/null','w'],2=>['file','/dev/null','w']], $pipes);
                if (!is_resource($process) || proc_close($process) !== 0) throw new RuntimeException('文件备份失败。');
                $name .= '.tar.gz';
            }
            // 只发布已写完且退出成功的备份，失败时 finally 删除临时文件。
            if (!rename($tmp, $dest.'/'.$name)) throw new RuntimeException('备份写入失败。');
            return $name;
        } finally {
            if (is_file($tmp)) unlink($tmp);
        }
    }

    /** 只列出备份根目录各分类中已发布的普通文件，结果为“类别/文件名”。 */
    public static function listing($root): array
    {
        $backups = [];
        foreach (glob(rtrim($root, '/').'/*/*') ?: [] as $path) {
            if (is_file($path) && !is_link($path)) $backups[] = basename(dirname($path)).'/'.basename($path);
        }
        return $backups;
    }
}
