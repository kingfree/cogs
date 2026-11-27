<?php
/**
 * 为后台生成数据库或文件备份。admin/backup.php 从 TYPES 生成表单，admin/dobackup.php 调用 create。
 * 例：$name = Backup::create('usercode', 'weekly', $SET, $cfg);
 * 备份名在压缩全部完成后才出现在 listing() 的结果中。
 */
final class Backup
{
    // type 是旧表单和备份子目录共同使用的名称。key 指定源目录所在的配置项。
    public const TYPES = [
        'database' => ['label'=>'数据库', 'suffix'=>'.sql.gz', 'key'=>null],  // 数据库从连接配置导出，无需指定源目录。
        'usercode' => ['label'=>'用户代码', 'suffix'=>'.tar.gz', 'key'=>'dir_source'],  // 普通提交与比赛提交分开备份，恢复时沿用各自的目录。
        'compcode' => ['label'=>'比赛代码', 'suffix'=>'.tar.gz', 'key'=>'dir_competition'],
        'testdata' => ['label'=>'测试数据', 'suffix'=>'.tar.gz', 'key'=>'testdata'],  // 测试数据目录由部署配置提供。
    ];

    /** 按配置导出文件；返回分类目录内的文件名。标签错误与实际写入失败分别报告。 */
    public static function create(string $type, string $label, array $settings, array $config): string
    {
        $kind = self::TYPES[$type] ?? null;  // 先按旧表单传来的种类查到对应的备份规则。
        if (!$kind || !preg_match('/^[A-Za-z0-9_][A-Za-z0-9_.-]{0,99}$/D', $label)) {  // 标签最多 100 个字符，禁止用点开头或带目录分隔符。
            throw new DomainException('备份名称或种类无效。');  // 参数错误由表单处理页作为 400 返回。
        }

        $root = $settings['dir_databackup'] ?? '';  // 所有类别都写到管理员指定的备份根目录。
        if ($root === '') throw new RuntimeException('请先设置备份目录。');  // 路径为空时中止，避免意外写入文件系统根目录。
        $directory = rtrim($root, '/').'/'.$type;  // 按正式类别名建子目录，标签只参与末尾的文件名。
        if (is_link($directory)) throw new RuntimeException('备份分类目录不能是符号链接。');  // 拒绝跟随分类链接，以免备份落到其他目录。
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {  // 目录仅对进程所属用户开放；并发请求刚刚建好目录也可以继续。
            throw new RuntimeException('不能创建备份目录。');
        }

        // 临时文件和成品在同一目录，成功后 rename 一次即可对外可见。
        $temporary = tempnam($directory, '.backup-');  // 点号前缀让备份列表跳过正在压缩的内容。
        if ($temporary === false) throw new RuntimeException('不能创建临时备份文件。');  // 没有临时输出文件时不启动导出程序。
        try {
            // 临时文件无法建在指定目录时，PHP 可能退回系统临时目录。
            if (realpath(dirname($temporary)) !== realpath($directory)) throw new RuntimeException('备份目录不可写。');
            if ($type === 'database') {  // 数据库走 SQL 快照，另外三类都从目录打包。
                self::dumpDatabase($config, $temporary);  // 先把完整 SQL 快照写入临时 gzip 文件。
            } else {
                // 测试数据由部署配置指定；两类源码目录由站点设置指定。
                $source = $type === 'testdata' ? $config[$kind['key']] : $settings[$kind['key']];
                self::archiveDirectory($source, $temporary);  // 将选定目录的文件写入临时 tar.gz。
            }
            $filename = $label.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(6)).$kind['suffix'];  // 随机尾缀避免同时备份同名标签时互相覆盖。
            if (!rename($temporary, $directory.'/'.$filename)) throw new RuntimeException('备份写入失败。');  // 同目录改名一次性发布，失败则继续走清理分支。
            return $filename;  // 管理页面只显示已发布的文件名。
        } finally {
            if (is_file($temporary)) unlink($temporary);  // 导出失败时删除半成品，改名成功后原路径已经消失。
        }
    }

    /** 将 mariadb-dump 的事务快照流式压缩，避免把整个数据库装入 PHP 内存。 */
    private static function dumpDatabase(array $config, string $output): void
    {
        $host = explode(';port=', $config['data_server'], 2);  // 拆开部署中沿用的“主机;port=端口”格式。
        $command = ['mariadb-dump', '--no-defaults', '--single-transaction', '--host='.$host[0],  // 导出事务快照，并忽略机器上的客户端默认配置。
            '--port='.($host[1] ?? 3306), '--user='.$config['data_uid'], '--databases', $config['data_database']];  // 省略端口时使用 3306，仅备份站点配置的库。
        $stdio = [0=>['file','/dev/null','r'], 1=>['pipe','w'], 2=>['file','/dev/null','w']];  // 用管道接收 SQL，其他流不发到管理页面。
        $environment = array_merge(getenv(), ['MYSQL_PWD'=>$config['data_pwd']]);  // 密码放在子进程环境中，避免出现在命令参数或页面上。
        $process = proc_open($command, $stdio, $pipes, null, $environment);  // 参数数组直接启动导出工具，路径和库名各占一个参数。
        if (!is_resource($process)) throw new RuntimeException('不能启动数据库备份。');  // 启动失败时没有可读取的快照。

        $compressed = gzopen($output, 'wb');  // 用 gzip 覆写尚未公开的临时文件。
        try {
            $written = $compressed !== false && stream_copy_to_stream($pipes[1], $compressed) !== false;  // 边读 SQL 边写压缩流，保留管道复制失败的结果。
        } finally {
            fclose($pipes[1]);  // 先释放导出程序的输出管道，再等待它退出。
            $finished = $compressed !== false && gzclose($compressed);  // 关闭压缩流才会写完 gzip 尾部，收尾失败也要丢弃文件。
            $status = proc_close($process);  // 拿到导出进程的真实退出码，防止把不完整的 SQL 当备份。
        }
        if (!$written || !$finished || $status !== 0) throw new RuntimeException('数据库备份失败。');  // 复制、gzip 收尾和导出进程都成功，才能发布。
    }

    /** 相对于源目录写入 tar，恢复时无需复刻原机器的绝对路径。 */
    private static function archiveDirectory(string $source, string $output): void
    {
        $command = ['tar', '-czf', $output, '-C', $source, '.'];  // 归档当前源目录下的全部内容，条目保留相对路径。
        $stdio = [0=>['file','/dev/null','r'], 1=>['file','/dev/null','w'], 2=>['file','/dev/null','w']];  // tar 直接写指定文件，后台请求无需接管输入输出。
        $process = proc_open($command, $stdio, $pipes);  // 直接启动 tar，源目录作为独立参数传入。
        if (!is_resource($process) || proc_close($process) !== 0) throw new RuntimeException('文件备份失败。');  // 工具无法启动或报告失败时，调用方会丢弃临时归档。
    }

    /** 列出已发布的普通文件，兼容已有备份名；临时文件、目录及符号链接不列出。 */
    public static function listing(string $root): array
    {
        if ($root === '' || !is_dir($root)) return [];  // 首次部署尚无备份目录时，管理页显示空列表。
        $backups = [];
        foreach (glob(rtrim($root, '/').'/*/*') ?: [] as $path) {  // 遍历分类下面的条目；点号开头的临时文件不会匹配。
            if (is_file($path) && !is_link($path) && !is_link(dirname($path))) {  // 成品和所在分类都不能是链接，以免列出根目录外的文件。
                $backups[] = basename(dirname($path)).'/'.basename($path);  // 只显示“分类/文件名”，保留以前版本创建的备份名。
            }
        }
        return $backups;  // 返回相对名称，由页面负责换行和转义。
    }
}
