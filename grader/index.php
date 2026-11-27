<?php
// 评测机 HTTP 入口。GET /grader/ 读取机器状态，网页判题流程用签名的 POST 请求申请和操作租约。
require_once dirname(__DIR__).'/include/grader-protocol.php';
require_once dirname(__DIR__).'/features/Archive.php';
require_once __DIR__.'/Worker.php';
require_once __DIR__.'/Sandbox.php';
require_once __DIR__.'/compiler.php';
require_once __DIR__.'/checker.php';
require_once __DIR__.'/testcase.php';

// 浏览器 GET 只显示机器状态；下方 POST 先验签，再校验动作字段，最后才能交给 Worker。
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $status = Worker::configured()->handle(['action'=>'state']);
        header('Content-Type: text/plain; charset=UTF-8');
        echo '评测机 '.$status['name'].' · '.$status['state'].' · 已评测 '.$status['cnt'].' 次';
    } catch (Throwable $error) { http_response_code(503); echo '评测机未就绪。'; }
    exit;
}
if (!graderSignatureValid($_POST, getenv('COGS_GRADER_KEY'), time())) { http_response_code(403); exit; }
$query = array_decode($_POST['query']);
if (!graderRequestAllowed($query)) { http_response_code(400); exit; }
try {
    $result = Worker::configured()->handle($query);
    echo '<return>'.array_encode($result);
} catch (DomainException $error) {
    echo '<return>'.array_encode(['state'=>'error', 'compilesucc'=>0, 'runerr'=>1, 'msg'=>$error->getMessage(), 'score'=>0]);
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(503);
    echo '评测机未就绪。';
}
