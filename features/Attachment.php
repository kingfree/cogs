<?php
/**
 * 编辑器上传图片、文档和媒体。include/upload_json.inc.php 先检查上传权限，再把站内相对路径返回给编辑器。
 * 例：$path = Attachment::save('image', $_FILES['imgFile']);
 */
final class Attachment
{
    private const TYPES = [
        'media' => ['flv', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'],
        'file' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'zip', '7z', 'rar', 'gz', 'bz2'],
    ];

    private const IMAGE_FORMATS = [
        'gif' => IMAGETYPE_GIF, 'jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG, 'bmp' => IMAGETYPE_BMP,
    ];

    /** 接收 $_FILES 中的单个上传；成功返回 upload/ 开头的相对路径，拒绝时抛出异常供编辑器显示。 */
    public static function save(string $kind, array $upload): string
    {
        // PHP 接收上传后提供临时文件；限额按实际文件长度判定。
        $temporary = $upload['tmp_name'] ?? '';
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($temporary) ||
            !is_uploaded_file($temporary) || filesize($temporary) > 1000000) {
            throw new DomainException('请选择一个不超过 1 MB 的文件。');
        }
        // 将扩展名规范为小写，图片和普通附件各有允许的格式。
        $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
        if ($kind === 'image') {
            if (!isset(self::IMAGE_FORMATS[$extension])) throw new DomainException('不支持这种附件格式。');
            // 从文件内容读取真实尺寸与类型，不能仅相信上传文件名。
            $image = @getimagesize($temporary);
            if (!$image || $image[2] !== self::IMAGE_FORMATS[$extension] || $image[0] * $image[1] > 12000000) {
                throw new DomainException('图片内容、扩展名或尺寸无效。');
            }
        // 媒体和文档不走图片解码，按所选种类核对扩展名。
        } elseif (!in_array($extension, self::TYPES[$kind] ?? [], true)) {
            throw new DomainException('不支持这种附件格式。');
        }
        // 持久保存位置来自站点配置；对外地址仍沿用 upload/。
        $base = publicFilePath('upload');
        // 按种类和日期组织公开目录，实际文件名由服务器生成。
        $relative = $kind.'/'.date('Ymd');
        $directory = $base.'/'.$relative;
        // 类别和日期只可对应真实目录，防止经链接写到公开目录以外。
        if (is_link($base.'/'.$kind) || is_link($directory)) throw new DomainException('附件目录无效。');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('不能创建附件目录。');
        }
        // 给附件分配随机名，既避免重名也不使用上传者提供的路径。
        $leaf = bin2hex(random_bytes(16)).'.'.$extension;
        // 临时文件和最终附件在同一目录；完整保存并设置权限后才对外返回地址。
        $stage = tempnam($directory, '.attachment-');
        try {
            if (!move_uploaded_file($temporary, $stage) || !chmod($stage, 0644) ||
                !rename($stage, $directory.'/'.$leaf)) throw new RuntimeException('附件保存失败。');
        } finally {
            // 若移动或设权中途失败，清掉尚未发布的临时文件。
            if (is_file($stage)) unlink($stage);
        }
        return 'upload/'.$relative.'/'.$leaf;
    }
}
