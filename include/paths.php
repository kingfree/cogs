<?php
/**
 * 处理安装在域名根目录或子目录的站内链接。模板需要普通 URL，操作成功或失败的提示页接收编码过的返回地址。
 * 例：$link = 路径('problem/index.php');
 */
/** 从当前脚本所在目录出发，计算到目标路径的相对地址。 */
function pathconvert($current, $target)
{
    $from = explode('/', trim(str_replace('\\', '/', dirname($current)), '/'));
    $to = explode('/', trim(str_replace('\\', '/', $target), '/'));
    while ($from && $to && $from[0] === $to[0]) { array_shift($from); array_shift($to); }
    return str_repeat('../', count($from)).implode('/', $to).(str_ends_with($target, '/') ? '/' : '');
}

/** 模板把站内路径变为相对链接，支持在子目录部署。 */
function 路径($path)
{
    global $SET;
    return pathconvert($SET['cur'], $SET['base'].$path);
}

/** 将带站点前缀的返回路径编码，传给 提示 或 异常。 */
function 取路径($path)
{
    global $SET;
    return base64_encode('/'.$SET['global_root'].$path);
}
