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
    // 密钥由两端部署时配置。缺少密钥就退出，避免把源码请求当作未签名表单送出。
    $key = getenv('COGS_GRADER_KEY');
    if (!$key || strlen($key) < 32) return 'error (评测密钥未配置)';
    // 先生成唯一的报文串，再对相同的字节和发送时刻签名，避免两边解释不同。
    $packet = array_encode($query);
    $timestamp = (string)time();
    $body = http_build_query(array('query' => $packet, 'time' => $timestamp,
        'signature' => graderSignature($timestamp, $packet, $key)));
    // 禁止跟随重定向，以免把签名和源码交给其他地址；等待时间限制覆盖一次编译或一个测试点。
    $context = stream_context_create(array('http' => array(
        'method' => 'POST', 'follow_location' => 0, 'timeout' => 60,
        'header' => "Content-Type: application/x-www-form-urlencoded\r\nConnection: close\r\n",
        'content' => $body
    )));
    // 旧评测机的正常回复带 <return> 前缀。网络错误和代理错误页面均不会交给调用方当作判题结果。
    $response = @file_get_contents($url, false, $context);
    if ($response === false || substr($response, 0, 8) !== '<return>') return 'error (评测机响应无效)';
    return substr($response, 8);
}
