<?php
// 发送一次评测操作。禁止跟随重定向，避免将已签名的请求转发给另一台服务器。
function httpsocket($url, $query) {
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
