<?php
function rfile($fp) {
    $out="";
    if (is_resource($fp)) {
        while (!feof($fp))
            $out.= fgets($fp, 1024000);
    }
    return $out;
}

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

function getextend($file_name) {
    $extend = pathinfo($file_name);
    $extend = strtolower($extend["extension"]);
    return $extend;
}
