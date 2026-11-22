<?php
// 测试数据由题目文件名定位。例 TestData::file('sum', 'sum1.in')。
final class TestData
{
    public static function name($name): bool
    {
        return is_string($name) && preg_match('/^[A-Za-z0-9_][A-Za-z0-9_-]{0,79}$/D', $name);
    }

    public static function leaf($name): bool
    {
        return is_string($name) && preg_match('/^[A-Za-z0-9_][A-Za-z0-9_.-]{0,149}$/D', $name);
    }

    public static function root(): string
    {
        global $cfg;
        $root = realpath($cfg['testdata']);
        if (!$root || !is_dir($root)) throw new DomainException('未配置测试数据目录。');
        return $root;
    }

    public static function directory($name, $create = false): string
    {
        if (!self::name($name)) throw new DomainException('题目文件名无效。');
        $dir = self::root().'/'.$name;
        if (is_link($dir)) throw new DomainException('不支持符号链接。');
        if ($create && !is_dir($dir) && !mkdir($dir, 0750) && !is_dir($dir)) throw new DomainException('不能创建数据目录。');
        return $dir;
    }

    public static function file($name, $file): string
    {
        if (!self::leaf($file)) throw new DomainException('数据文件名无效。');
        $path = self::directory($name).'/'.$file;
        if (is_link($path)) throw new DomainException('不支持符号链接。');
        return $path;
    }

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
            foreach ($files as $file => $contents) self::store($name, $file, $contents);
        } finally {
            $zip->close();
        }
    }

    public static function sendCase($name, $point, $ext): void
    {
        if (!self::name($name) || $point < 1 || !in_array($ext, ['in','ans'], true)) {
            http_response_code(400);
            exit;
        }
        if (!Privilege::allows('查看数据') && ($_SESSION['failed_cases'][$name] ?? null) !== $point) {
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
