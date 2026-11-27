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
        if (!$files) { http_response_code(404); exit('没有可下载的文件。'); }   // 请求没有选中可下载文件时，不产生空归档。
        $temp = tempnam(sys_get_temp_dir(), 'cogs-zip-');               // 并行下载各写自己的临时压缩包。
        if ($temp === false) throw new RuntimeException('不能创建临时压缩包。');   // 没有可写的临时路径时停止下载。
        $zip = new ZipArchive();
        try {
            if ($zip->open($temp, ZipArchive::OVERWRITE) !== true) throw new RuntimeException('不能创建压缩包。');
            foreach ($files as $entry => $path) {   // 调用者提供已获准的源路径和最终 ZIP 成员名。
                if (is_file($path) && !is_link($path) && !$zip->addFile($path, $entry)) {
                    throw new RuntimeException('不能添加文件。');
                }
            }
            if (!$zip->close() || !is_file($temp)) throw new RuntimeException('不能写入压缩包。');   // 等归档完整写盘再发送下载响应。
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($name).'"');
            readfile($temp);   // 直接发送压缩包，避免把大文件整份读进内存。
        } finally {
            if (is_file($temp)) unlink($temp);   // 发送出错也要删除本次的临时包。
        }
    }

    /** 检查整个 ZIP 的路径、成员类型、总解压大小和条目数，成功返回只读句柄，由调用方关闭。 */
    public static function open(string $path, int $maxBytes, int $maxFiles): ZipArchive
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) throw new DomainException('ZIP 格式无效。');   // 评测读取选手答案，不修改原始 ZIP。
        try {
            if ($zip->numFiles > $maxFiles) throw new DomainException('压缩包内文件过多。');
            $totalBytes = 0;                                              // 题目限制的是整个压缩包展开后的总量。
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->statIndex($index);                          // 读取成员名称及解压后的大小。
                if (!$entry || !$zip->getExternalAttributesIndex($index, $os, $attributes)) {
                    throw new DomainException('不能读取压缩包成员信息。');
                }
                $name = $entry['name'];   // 本地落盘前必须检查包内原始名称。
                $totalBytes += $entry['size'];                             // 累计整个包，多个小文件也计入上限。
                $type = ($attributes >> 16) & 0170000;                     // Unix 高 16 位的文件类型可区分链接和普通文件。
                $safeType = $os !== ZipArchive::OPSYS_UNIX || in_array($type, [0, 0040000, 0100000], true);
                if (!$safeType || $totalBytes > $maxBytes || str_contains($name, '\\') || str_starts_with($name, '/') ||
                    preg_match('~(^|/)(\.{1,2})(/|$)|[[:cntrl:]]~', $name)) {
                    throw new DomainException('压缩包包含无效路径、文件类型或超出大小限制。');   // 任一有问题的成员都会使整包校验失败。
                }
            }
        } catch (Throwable $error) {
            $zip->close();                                                 // 失败时没有调用者接手，校验处负责关闭。
            throw $error;
        }
        return $zip;
    }
}
