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

    /** 解析已配置的数据根目录；未配置或目录不存在时抛出异常。 */
    public static function root(): string
    {
        global $cfg;
        $root = realpath($cfg['testdata']);
        if (!$root || !is_dir($root)) throw new DomainException('未配置测试数据目录。');
        return $root;
    }

    /** 返回题目的数据目录路径；只有 create 为真时才创建目录。 */
    public static function directory($name, $create = false): string
    {
        if (!self::name($name)) throw new DomainException('题目文件名无效。');
        $dir = self::root().'/'.$name;
        if (is_link($dir)) throw new DomainException('不支持符号链接。');
        if ($create && !is_dir($dir) && !mkdir($dir, 0750) && !is_dir($dir)) throw new DomainException('不能创建数据目录。');
        return $dir;
    }

    /** 返回一个安全路径，不保证文件已经存在。题目目录和末级文件都不得是符号链接。 */
    public static function file($name, $file): string
    {
        if (!self::leaf($file)) throw new DomainException('数据文件名无效。');
        $path = self::directory($name).'/'.$file;
        if (is_link($path)) throw new DomainException('不支持符号链接。');
        return $path;
    }

    /** 列出可用普通文件，以相对文件名为键，自然排序使第 2 点出现在第 10 点之前。 */
    public static function files(string $name): array
    {
        $dir = self::directory($name);
        $files = [];
        if (!is_dir($dir)) return $files;
        foreach (new DirectoryIterator($dir) as $entry) {
            if ($entry->isFile() && !$entry->isLink() && self::leaf($entry->getFilename())) {
                $files[$entry->getFilename()] = $entry->getPathname();
            }
        }
        ksort($files, SORT_NATURAL);
        return $files;
    }

    /** 在同目录写好临时文件、设好权限，再替换目标，避免读者读到半个测试点。 */
    public static function store($name, $file, string $contents): void
    {
        self::directory($name, true);
        $path = self::file($name, $file);
        $temp = tempnam(dirname($path), '.upload-');
        try {
            if (file_put_contents($temp, $contents) !== strlen($contents) || !chmod($temp, 0640) || !rename($temp, $path)) {
                throw new DomainException('数据文件写入失败。');
            }
        } finally {
            if (is_file($temp)) unlink($temp);
        }
    }

    /** 先核对整个题包，再逐文件安装。allowChecker 必须来自修改题目权限，普通出题人只能传数据。 */
    public static function importZip($name, $count, $path, bool $allowChecker): void
    {
        $zip = Archive::open($path, 64 * 1024 * 1024, 4096);
        $entries = [];
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $full = $zip->getNameIndex($i);
                if (str_ends_with($full, '/')) continue;
                $leaf = basename($full);
                if (!self::leaf($leaf) || isset($entries[$leaf])) throw new DomainException('数据文件名无效或重复。');
                $entries[$leaf] = $i;
            }
            // 有些旧题包以 data.txt 定义起始编号和输入输出模板；映射到从 1 开始的本站命名。
            $mapping = [];
            if (isset($entries['data.txt'])) {
                $format = preg_split('/\s+/', trim($zip->getFromIndex($entries['data.txt'])));
                if (count($format) < 3 || !ctype_digit($format[0])) throw new DomainException('data.txt 格式错误。');
                for ($n = 1; $n <= $count; $n++) {
                    $index = (int)$format[0] + $n - 1;
                    $mapping[str_replace('#', $index, $format[1])] = $name.$n.'.in';
                    $mapping[str_replace('#', $index, $format[2])] = $name.$n.'.ans';
                }
            }
            $files = [];
            foreach ($entries as $leaf => $i) {
                if ($leaf === 'data.txt') continue;
                $target = $mapping[$leaf] ?? $leaf;
                if ($target !== 'sample.out') $target = preg_replace('/\.out$/D', '.ans', $target);
                $checker = preg_match('/^(?:spj|cena|interactor)\.(?:c|cc|cpp|pas|pp)$|^plugin\.php$/D', $target);
                if ($checker && !$allowChecker) throw new DomainException('需要修改题目权限才能上传判题器。');
                if (!$checker && !preg_match('/\.(?:in|ans|out|txt)$/D', $target)) throw new DomainException('数据文件类型无效。');
                if (isset($files[$target])) throw new DomainException('数据文件名重复。');
                $contents = $zip->getFromIndex($i);
                if ($contents === false) throw new DomainException('压缩数据损坏。');
                $files[$target] = $contents;
            }
            // 整包的条目都通过检查后才开始写入，单个文件由 store 原子替换。
            foreach ($files as $file => $contents) self::store($name, $file, $contents);
        } finally {
            $zip->close();
        }
    }

    /** 按题目、点号和 in/ans 返回文件；无查看数据权限的选手只能取会话记住的失败点。 */
    public static function sendCase($name, $point, $ext): void
    {
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
        $leaf = $name.$point.'.'.$ext;
        $path = self::file($name, $leaf);
        if (!is_file($path)) { http_response_code(404); exit; }
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$leaf.'"');
        readfile($path);
    }
}
