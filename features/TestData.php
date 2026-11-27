<?php
/**
 * 题目数据以题目的英文文件名分目录。problem/testdata.php 负责判断管理权限，这里限制文件名、存储位置和写入方式。
 * 例：$inputs = TestData::files('sum');  // 返回文件名到本机路径的对应表。
 */
final class TestData
{
    /** 验证题目的英文目录名。编号、字母、连字符与下划线的组合必须是一个路径段。 */
    public static function name($name): bool
    {
        return is_string($name) && preg_match('/^[A-Za-z0-9_][A-Za-z0-9_-]{0,79}$/D', $name);
    }

    /** 验证题目目录内的单个文件名；允许扩展名，禁止路径分隔符和前导点。 */
    public static function leaf($name): bool
    {
        return is_string($name) && preg_match('/^[A-Za-z0-9_][A-Za-z0-9_.-]{0,149}$/D', $name);
    }

    /** 识别 COGS 能运行的判题器源码文件名，ZIP 上传和 FPS 题包往返使用同一份名单。 */
    public static function checkerFile(string $file): bool
    {
        return $file === 'plugin.php' || preg_match('/^(?:spj|cena|interactor)\.(?:c|cc|cpp|pas|pp)$/D', $file);
    }

    /** 解析已配置的数据根目录；未配置或目录不存在时抛出异常。 */
    public static function root(): string
    {
        global $cfg;
        // 配置项指向评测机和后台共用的数据根，先固定为本机实际目录。
        $root = realpath($cfg['testdata']);
        if (!$root || !is_dir($root)) throw new DomainException('未配置测试数据目录。');
        return $root;
    }

    /** 返回题目的数据目录路径；只有 create 为真时才创建目录。 */
    public static function directory($name, $create = false): string
    {
        if (!self::name($name)) throw new DomainException('题目文件名无效。');
        // 题目只能占用根下一个子目录，不能借助符号链接离开它。
        $dir = self::root().'/'.$name;
        if (is_link($dir)) throw new DomainException('不支持符号链接。');
        // 浏览不会建目录，首次上传和主动创建才会建。
        if ($create && !is_dir($dir) && !mkdir($dir, 0750) && !is_dir($dir)) throw new DomainException('不能创建数据目录。');
        return $dir;
    }

    /** 返回一个安全路径，不保证文件已经存在。题目目录和末级文件都不得是符号链接。 */
    public static function file($name, $file): string
    {
        if (!self::leaf($file)) throw new DomainException('数据文件名无效。');
        // 先验证题目目录，再把一个已验证的文件名拼在其下。
        $path = self::directory($name).'/'.$file;
        if (is_link($path)) throw new DomainException('不支持符号链接。');
        return $path;
    }

    /** 列出可用普通文件，以相对文件名为键，自然排序使第 2 点出现在第 10 点之前。 */
    public static function files(string $name): array
    {
        $dir = self::directory($name);
        $files = [];
        // 没有数据的题返回空列表，后台仍可显示上传表单。
        if (!is_dir($dir)) return $files;
        foreach (new DirectoryIterator($dir) as $entry) {
            if ($entry->isFile() && !$entry->isLink() && self::leaf($entry->getFilename())) {
                // 用可展示的短文件名索引后台用于读写的绝对路径。
                $files[$entry->getFilename()] = $entry->getPathname();
            }
        }
        // 按编号自然排序，让第十号测试排在第二号后。
        ksort($files, SORT_NATURAL);
        return $files;
    }

    /** 后台根目录只列出有效的题目文件夹，与单题下载使用相同的路径规则。 */
    public static function folders(): array
    {
        $folders = [];
        foreach (new DirectoryIterator(self::root()) as $entry) {
            // 过滤目录遍历产生的点目录、符号链接和不合法的旧条目。
            if ($entry->isDir() && !$entry->isLink() && self::name($entry->getFilename())) {
                $folders[] = $entry->getFilename();
            }
        }
        // 后台按人习惯的数字顺序显示题目目录。
        natcasesort($folders);
        return $folders;
    }

    /** 管理员编辑数据文件，返回完成操作后应打开的题目目录。权限由后台入口先检查。 */
    public static function editFiles(string $problem, array $fields, array $upload): string
    {
        // 各个表单共用动作字段；文件名始终以当前题目为起点。
        $action = $fields['action'] ?? '';
        $leaf = $fields['file'] ?? '';
        switch ($action) {
            case 'mkdir':
                // 创建目录时，表单填入的是新题目的英文名。
                $problem = $fields['problem'] ?? '';
                self::directory($problem, true);
                break;
            case 'upload':
                // 上传只接受本次请求的文件，限额按单个数据文件计算。
                if (!is_uploaded_file($upload['tmp_name'] ?? '')) throw new DomainException('请选择上传的数据文件。');
                if ($upload['size'] > 16777216) throw new DomainException('单个文件超过 16 MiB。');
                // 沿用上传者给的测试点文件名，由 store 校验并完整替换。
                self::store($problem, $upload['name'], file_get_contents($upload['tmp_name']));
                break;
            case 'save':
                // 在线编辑器的正文可为空，保存空文件也是合法操作。
                self::store($problem, $leaf, $fields['contents'] ?? '');
                break;
            case 'delete':
                // 删除需精确命中单个已存在的普通文件。
                $source = self::file($problem, $leaf);
                if (!is_file($source) || !unlink($source)) throw new DomainException('删除文件失败。');
                break;
            case 'copy':
            case 'move':
                // 源在当前题，目标可以在另外一道已经建好目录的题。
                $source = self::file($problem, $leaf);
                $target = self::file($fields['target_problem'] ?? $problem, $fields['target'] ?? '');
                if (!is_file($source) || file_exists($target)) throw new DomainException('源文件不存在或目标文件已存在。');
                // 复制保留源文件，移动才会将它从源目录移除。
                $saved = $action === 'copy' ? copy($source, $target) : rename($source, $target);
                if (!$saved) throw new DomainException('文件操作失败。');
                break;
            default:
                // 未识别的动作不能被当成上传或覆盖文件处理。
                throw new DomainException('数据操作无效。');
        }
        // 创建目录后要返回新目录，其他动作仍返回当前题的数据列表。
        return $problem;
    }

    /** 在同目录写好临时文件、设好权限，再替换目标，避免读者读到半个测试点。 */
    public static function store($name, $file, string $contents): void
    {
        // 首次保存允许建立题目目录。文件自身另行检查，不能跟随符号链接。
        self::directory($name, true);
        $path = self::file($name, $file);
        // 临时文件与目标位于同一目录，最后一次重命名可原子替换。
        $temp = tempnam(dirname($path), '.upload-');
        try {
            if (file_put_contents($temp, $contents) !== strlen($contents) || !chmod($temp, 0640) || !rename($temp, $path)) {
                throw new DomainException('数据文件写入失败。');
            }
        } finally {
            // 写入失败也要清掉未安装的暂存文件。
            if (is_file($temp)) unlink($temp);
        }
    }

    /** 先核对整个题包，再逐文件安装。allowChecker 必须来自修改题目权限，普通出题人只能传数据。 */
    public static function importZip($name, $count, $path, bool $allowChecker, ?callable $commitProblem = null): void
    {
        // 先检查压缩包大小和条目数，防止大量文件占满题库。
        $zip = Archive::open($path, 64 * 1024 * 1024, 4096);
        $entries = [];
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                // 忽略 ZIP 内的目录层级，所有测试点安装到本题的一层目录。
                $full = $zip->getNameIndex($i);
                if (str_ends_with($full, '/')) continue;
                $leaf = basename($full);
                // 不同子目录里的同名文件展开后会冲突，在写入前拒绝。
                if (!self::leaf($leaf) || isset($entries[$leaf])) throw new DomainException('数据文件名无效或重复。');
                $entries[$leaf] = $i;
            }
            // 有些旧题包以 data.txt 定义起始编号和输入输出模板；映射到从 1 开始的本站命名。
            $mapping = [];
            if (isset($entries['data.txt'])) {
                $format = preg_split('/\s+/', trim($zip->getFromIndex($entries['data.txt'])));
                // 至少需要起始编号、输入文件模板和标准答案模板。
                if (count($format) < 3 || !ctype_digit($format[0])) throw new DomainException('data.txt 格式错误。');
                // 起始编号由旧包决定；本站的第一组数据固定叫题名加 1。
                for ($n = 1; $n <= $count; $n++) {
                    $index = (int)$format[0] + $n - 1;
                    $mapping[str_replace('#', $index, $format[1])] = $name.$n.'.in';
                    // 答案使用同一编号，与输入构成一个测试点。
                    $mapping[str_replace('#', $index, $format[2])] = $name.$n.'.ans';
                }
            }
            $files = [];
            foreach ($entries as $leaf => $i) {
                // data.txt 已用于解析命名，本身不作为评测点安装。
                if ($leaf === 'data.txt') continue;
                $target = $mapping[$leaf] ?? $leaf;
                // 普通标准输出统一为 .ans，展示用样例仍叫 sample.out。
                if ($target !== 'sample.out') $target = preg_replace('/\.out$/D', '.ans', $target);
                $checker = self::checkerFile($target);
                // 判题器带有可执行逻辑，需要题目管理权限；其他条目只接受数据扩展名。
                if ($checker && !$allowChecker) throw new DomainException('需要修改题目权限才能上传判题器。');
                if (!$checker && !preg_match('/\.(?:in|ans|out|txt)$/D', $target)) throw new DomainException('数据文件类型无效。');
                // 兼容名转换也可能导致冲突，最终安装名必须唯一。
                if (isset($files[$target])) throw new DomainException('数据文件名重复。');
                $contents = $zip->getFromIndex($i);
                if ($contents === false) throw new DomainException('压缩数据损坏。');
                // 先在限额内读齐数据，压缩流损坏时不开始安装。
                $files[$target] = $contents;
            }
            // 所有目标和数据都已确定，再一起安装并提交题目。数据库拒绝提交时恢复原来的测试点。
            self::installFiles($name, $files, $commitProblem);
        } finally {
            $zip->close();
        }
    }

    /** 在同一文件系统暂存并替换数据包文件，题目提交失败时恢复已替换的文件。 */
    private static function installFiles(string $name, array $files, ?callable $commitProblem): void
    {
        // 未选任何文件的合法空包只需完成题面保存，不创建数据目录。
        if (!$files) {
            if ($commitProblem) $commitProblem();
            return;
        }
        // 有内容的题包需在本题真实目录里暂存，才能同盘替换。
        $directory = self::directory($name, true);
        $stage = $directory.'/.import-'.bin2hex(random_bytes(12));   // 后台列表和评测机均忽略暂存目录。
        if (!mkdir($stage, 0700)) throw new DomainException('不能暂存测试数据。');
        $installed = [];
        $restored = true;                                  // 恢复失败时须保留暂存中的原件。
        try {
            foreach ($files as $file=>$contents) {
                $target = self::file($name, $file);         // 不跟随数据目录或现有测试点的符号链接。
                if (file_exists($target)) {
                    if (!is_file($target)) throw new DomainException('不能替换非普通文件。');
                    $backup = $stage.'/'.$file.'.old';      // 每个覆盖目标各有一份可恢复的原件。
                    // 同盘的旧文件优先保留硬链接；不支持硬链接的存储卷使用独立副本。
                    if (!@link($target, $backup) && (!copy($target, $backup) || !chmod($backup, fileperms($target) & 0777))) {
                        throw new DomainException('不能保留旧测试点。');
                    }
                }
                // 先在目标目录所属的同一文件系统写齐全部新文件；空间不足时旧题数据仍可读取。
                $next = $stage.'/'.$file.'.new';
                if (file_put_contents($next, $contents) !== strlen($contents) || !chmod($next, 0640)) {
                    throw new DomainException('数据文件写入失败。');
                }
            }
            // 每个新测试点已经完整写好，至此才开始替换站点正在读取的文件。
            foreach (array_keys($files) as $file) {
                if (!rename($stage.'/'.$file.'.new', $directory.'/'.$file)) throw new DomainException('不能安装测试点。');
                $installed[] = $file;                      // 只回退已经发布成功的测试点。
            }
            if ($commitProblem) $commitProblem();          // 数据可读后才提交题目记录，两者的失败走同一条恢复路径。
        } catch (Throwable $error) {
            // 按已成功替换的文件名找回原件，尚在暂存区的文件从未对外可见。
            foreach ($installed as $file) {
                $old = $stage.'/'.$file.'.old';
                // 旧点换回原文件；本次才增加的点直接移除。
                $target = $directory.'/'.$file;
                $reverted = is_file($old) ? rename($old, $target) : unlink($target);
                if (!$reverted) $restored = false;
            }
            // 无法恢复的底层存储需要保留原始备份，以便管理员找回。
            if (!$restored) throw new RuntimeException('恢复旧测试点失败，原件保留在 '.$stage, 0, $error);
            throw $error;
        } finally {
            // 安装成功或完整回退后才删除暂存，避免泄漏旧标准答案或留下占用空间的副本。
            if ($restored) {
                foreach (new FilesystemIterator($stage) as $entry) unlink($entry->getPathname());
                rmdir($stage);
            }
        }
    }

    /** 按题目、点号和 in/ans 返回文件；无查看数据权限的选手只能取会话记住的失败点。 */
    public static function sendCase($name, $point, $ext): void
    {
        // 失败点的下载地址只接受正整数点号以及输入或标准答案。
        if (!self::name($name) || $point < 1 || !in_array($ext, ['in','ans'], true)) {
            http_response_code(400);
            exit;
        }
        // 错题记录仅授权这一测试点；离开小组后，旧会话里的记录也要重新检查题目可见性。
        if (!Privilege::allows('查看数据') &&
            (($_SESSION['failed_cases'][$name] ?? null) !== $point || !Problem::readableFilename($name))) {
            http_response_code(403);
            exit;
        }
        // 通过授权后才透出对应文件是否存在。
        $leaf = $name.$point.'.'.$ext;
        $path = self::file($name, $leaf);
        if (!is_file($path)) { http_response_code(404); exit; }  // 已获下载权限，但题库里没有这个点时按文件缺失返回。
        // 使用纯文本附件发送单个点，文件内容不会被当作网页显示。
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$leaf.'"');
        readfile($path);
    }
}
