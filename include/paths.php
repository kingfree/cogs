<?php
// 站点可以挂在域名根目录或任意子目录。例 路径('problem/index.php')。
function pathconvert($current, $target)
{
    $from = explode('/', trim(str_replace('\\', '/', dirname($current)), '/'));
    $to = explode('/', trim(str_replace('\\', '/', $target), '/'));
    while ($from && $to && $from[0] === $to[0]) { array_shift($from); array_shift($to); }
    return str_repeat('../', count($from)).implode('/', $to).(str_ends_with($target, '/') ? '/' : '');
}

function 路径($path)
{
    global $SET;
    return pathconvert($SET['cur'], $SET['base'].$path);
}

function 取路径($path)
{
    global $SET;
    return base64_encode('/'.$SET['global_root'].$path);
}
