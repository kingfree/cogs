<?php
/** 模板通过 getfilesize(1536) 显示 1.5 KB。上传大小校验直接使用原始字节数。 */

/** 以 1024 进位显示字节，最大单位沿用 TB。 */
function getfilesize($bytes)
{
    foreach (['TB' => 4, 'GB' => 3, 'MB' => 2, 'KB' => 1] as $unit => $power) {
        $scale = 1024 ** $power; // 从最大单位往下选择，使列表保持可读。
        if ($bytes >= $scale) return round($bytes / $scale, 2).' '.$unit;
    }
    return $bytes.' B'; // 小于 1 KB 的文件直接显示原始字节数。
}
