<?php
function pathconvert($cur,$absp) { //当前文件，目标路径
    $cur = str_replace("\\","/",$cur);
    $absp = str_replace("\\","/",$absp);
    $sabsp=explode("/",$absp);
    $scur=explode("/",$cur);
    $la=count($sabsp)-1;
    $lb=count($scur)-1;
    $l=max($la,$lb);

    for ($i=0; $i<=$l; $i++) {
        if ($sabsp[$i]!=$scur[$i])
            break;
    }
    $k=$i-1;
    $path="";
    for ($i=1; $i<=($lb-$k-1); $i++)
        $path.="../";
    for ($i=$k+1; $i<=($la-1); $i++)
        $path.=$sabsp[$i]."/";
    $path.=$sabsp[$la];
    return $path;
}

function 路径($str) {
    global $SET;
    $str1 = $SET['base'].$str;
    return pathconvert($SET['cur'],$str1);
}

function 取路径($path) {
    global $SET;
    return base64_encode("/{$SET['global_root']}$path");
}
