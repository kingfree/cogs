<?php
/**
 * 网站向已配置的评测机发送一个签名操作。评测状态查询、申请租约、编译和单点评分都经过这里。
 * 例：$state = array_decode(httpsocket($graderAddress, ['action'=>'state']));
 */
function httpsocket($url, $query) {
    global $cfg;
    // 老数据库中 grader 代表本站评测目录。固定使用回环地址，避免由浏览器的 Host 决定签名请求的去向。
    if ($url === 'grader') $url = 'http://127.0.0.1/'.ltrim(trim($cfg['dir_root'], '/').'/grader/', '/');
    if (!preg_match('~^https?://~i', $url)) $url = 'http://'.$url;
    $key = getenv('COGS_GRADER_KEY');
    if (!$key || strlen($key) < 32) return 'error (评测密钥未配置)';
    $packet = array_encode($query);
    $timestamp = (string)time();
    $body = http_build_query(array('query' => $packet, 'time' => $timestamp,
        'signature' => hash_hmac('sha256', $timestamp."\n".$packet, $key)));
    $context = stream_context_create(array('http' => array(
        'method' => 'POST', 'follow_location' => 0, 'timeout' => 60,
        'header' => "Content-Type: application/x-www-form-urlencoded\r\nConnection: close\r\n",
        'content' => $body
    )));
    $response = @file_get_contents($url, false, $context);
    if ($response === false || substr($response, 0, 8) !== '<return>') return 'error (评测机响应无效)';
    return substr($response, 8);
}
