<?php
/**
 * 处理安装在域名根目录或子目录的站内链接。模板需要普通 URL，操作成功或失败的提示页接收编码过的返回地址。
 * 例：$link = 路径('problem/index.php');
 */
/** 从当前脚本所在目录出发，计算到目标路径的相对地址。 */
function pathconvert($current, $target)
{
    $from = explode('/', trim(str_replace('\\', '/', dirname($current)), '/')); // 以当前脚本所在的目录为起点。
    $to = explode('/', trim(str_replace('\\', '/', $target), '/')); // 目标目录按同一规则拆分。
    while ($from && $to && $from[0] === $to[0]) { array_shift($from); array_shift($to); } // 消去共同的安装路径，支持子目录部署。
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

/** 返回公开附件或图片的磁盘位置。旧站沿用原目录，新安装可将可写文件置于代码树外。 */
function publicFilePath(string $relative): string
{
    global $cfg;
    // 参数由应用选择，浏览器中的 URL 始终是 images/ 或 upload/，与物理挂载点无关。
    $directory = $cfg['public_files'] ?? dirname(__DIR__);
    if (!str_starts_with($directory, '/') && !preg_match('~^(?:[A-Za-z]:[/\\\\]|\\\\\\\\)~', $directory)) {
        throw new RuntimeException('public_files 需要填写绝对路径。'); // 避免从不同工作目录启动时读写到另一份附件。
    }
    return rtrim($directory, '/\\').'/'.$relative;
}
