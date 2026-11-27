<?php
/**
 * 旧网页显示下载大小、扩展名和文本内容时使用的文件函数。测试数据的路径与权限另由 TestData 处理。
 * 例：echo getfilesize(1536);  // 显示 1.5 KB。
 */
/** 从已打开的文件流读至末尾；关闭流由调用者负责。 */
function rfile($fp) {
    $out="";
    if (is_resource($fp)) {
        while (!feof($fp))
            $out.= fgets($fp, 1024000);
    }
    return $out;
}

/** 递归删除传入目录的内容与目录本身，只供调用者确认过的工作目录使用。 */
function deldir($dir) {
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);
    rmdir($dir);
}

/** 按 1024 进位显示文件大小，保留至多两位小数。 */
function getfilesize($bytes) {
    if ($bytes >= pow(2,40)) {
        $return = round($bytes / pow(1024,4), 2);
        $suffix = "TB";
    }
    elseif ($bytes >= pow(2,30)) {
        $return = round($bytes / pow(1024,3), 2);
        $suffix = "GB";
    }
    elseif ($bytes >= pow(2,20)) {
        $return = round($bytes / pow(1024,2), 2);
        $suffix = "MB";
    }
    elseif ($bytes >= pow(2,10)) {
        $return = round($bytes / pow(1024,1), 2);
        $suffix = "KB";
    }
    else {
        $return = $bytes;
        $suffix = "B";
    }
    $return .= " " . $suffix;
    return $return;
}

/** 以小写返回文件扩展名，供旧页面展示或分类。 */
function getextend($file_name) {
    $extend = pathinfo($file_name);
    $extend = strtolower($extend["extension"]);
    return $extend;
}
