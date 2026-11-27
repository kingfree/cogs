<?php
/**
 * 为授权下载打包文件，也为题目数据和选手答案检查 ZIP。调用者负责选择读者能看的文件。
 * 例：$zip = Archive::open($uploadedPath, 64 * 1024 * 1024, 4096);  // 读取完毕调用 $zip->close()。
 */
final class Archive
{
    /** 把已通过权限检查的文件发送给浏览器。$files 的键是包内名称，值是本机路径。 */
    public static function download(string $name, array $files): void
    {
        if (!$files) { http_response_code(404); exit('没有可下载的文件。'); }
        // 每次下载使用独立临时文件，多个选手可同时下载同名的 ZIP。
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

    /** 检查整个 ZIP 的路径、成员类型、总解压大小和条目数，成功返回只读句柄，由调用方关闭。 */
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
            // Unix 压缩包把文件类型放在高 16 位，借此区分普通文件、目录和链接。
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
