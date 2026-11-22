<?php
// ZIP 下载使用每次请求独立的临时文件。例 Archive::download('source.zip', ['sum.c' => $path])。
final class Archive
{
    public static function download(string $name, array $files): void
    {
        if (!$files) { http_response_code(404); exit('没有可下载的文件。'); }
        $temp = tempnam(sys_get_temp_dir(), 'cogs-zip-');
        $zip = new ZipArchive();
        try {
            if ($zip->open($temp, ZipArchive::OVERWRITE) !== true) throw new RuntimeException('不能创建压缩包。');
            foreach ($files as $entry => $path) {
                if (is_file($path) && !is_link($path) && !$zip->addFile($path, $entry)) {
                    throw new RuntimeException('不能添加文件。');
                }
            }
            if (!$zip->close() || !is_file($temp)) throw new RuntimeException('不能写入压缩包。');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($name).'"');
            readfile($temp);
        } finally {
            if (is_file($temp)) unlink($temp);
        }
    }

    // 只读普通文件。先检查整个目录和解压大小，再交给调用者消费内容。
    public static function open(string $path, int $maxBytes, int $maxFiles): ZipArchive
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) throw new DomainException('ZIP 格式无效。');
        $size = 0;
        if ($zip->numFiles > $maxFiles) throw new DomainException('压缩包内文件过多。');
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->statIndex($i);
            $name = $entry['name'];
            $zip->getExternalAttributesIndex($i, $os, $attributes);
            $type = ($attributes >> 16) & 0170000;
            $size += $entry['size'];
            if ($size > $maxBytes || str_contains($name, '\\') || str_starts_with($name, '/') ||
                preg_match('~(^|/)(\.{1,2})(/|$)|[[:cntrl:]]~', $name) ||
                ($os === ZipArchive::OPSYS_UNIX && !in_array($type, [0, 0040000, 0100000], true))) {
                throw new DomainException('压缩包包含无效路径、文件类型或超出大小限制。');
            }
        }
        return $zip;
    }
}
