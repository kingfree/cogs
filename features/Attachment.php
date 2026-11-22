<?php
// 编辑器上传的公开附件。例如 Attachment::save('image', $_FILES['imgFile']) 返回站内相对路径。
final class Attachment
{
    private const TYPES = [
        'image' => ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
        'media' => ['flv', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'],
        'file' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'zip', '7z', 'rar', 'gz', 'bz2'],
    ];

    public static function save(string $kind, array $upload): string
    {
        $temporary = $upload['tmp_name'] ?? '';
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($temporary) ||
            !is_uploaded_file($temporary) || filesize($temporary) > 1000000) {
            throw new DomainException('请选择一个不超过 1 MB 的文件。');
        }
        $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::TYPES[$kind] ?? [], true)) throw new DomainException('不支持这种附件格式。');
        if ($kind === 'image') {
            $expected = ['gif'=>IMAGETYPE_GIF, 'jpg'=>IMAGETYPE_JPEG, 'jpeg'=>IMAGETYPE_JPEG, 'png'=>IMAGETYPE_PNG, 'bmp'=>IMAGETYPE_BMP];
            $image = @getimagesize($temporary);
            if (!$image || $image[2] !== $expected[$extension] || $image[0] * $image[1] > 12000000) {
                throw new DomainException('图片内容、扩展名或尺寸无效。');
            }
        }
        $base = dirname(__DIR__).'/upload';
        $relative = $kind.'/'.date('Ymd');
        $directory = $base.'/'.$relative;
        if (is_link($base.'/'.$kind) || is_link($directory)) throw new DomainException('附件目录无效。');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('不能创建附件目录。');
        }
        $leaf = bin2hex(random_bytes(16)).'.'.$extension;
        $stage = tempnam($directory, '.attachment-');
        try {
            if (!move_uploaded_file($temporary, $stage) || !chmod($stage, 0644) ||
                !rename($stage, $directory.'/'.$leaf)) throw new RuntimeException('附件保存失败。');
        } finally {
            if (is_file($stage)) unlink($stage);
        }
        return 'upload/'.$relative.'/'.$leaf;
    }
}
