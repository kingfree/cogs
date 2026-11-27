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
        header('Content-Type: text/plain; charset=UTF-8'); // 机器名和状态只作为纯文本标签展示。
        echo '评测机 '.$status['name'].' · '.$status['state'].' · 已评测 '.$status['cnt'].' 次';
    } catch (Throwable $error) {
        // 无法读取配置或租约时，对状态探针报告服务未就绪。
        http_response_code(503);
        echo '评测机未就绪。';
    }
    exit;
}
// 先检查共享密钥对这份原始报文的签名。签名错误时不解析源码或访问工作目录。
if (!graderSignatureValid($_POST, getenv('COGS_GRADER_KEY'), time())) { http_response_code(403); exit; }
// 解码后的键值仍需按动作核验；字段错误直接给 HTTP 400，不启动编译器。
$query = array_decode($_POST['query']);
if (!graderRequestAllowed($query)) { http_response_code(400); exit; }
try {
    // Worker 在独占租约内执行动作，正常结果用历史网页识别的 <return> 前缀返回。
    $result = Worker::configured()->handle($query);
    echo '<return>'.array_encode($result);
} catch (DomainException $error) {
    // 缺少编译产物或题目判题器等错误用评测协议反馈，让网页显示具体原因。
    echo '<return>'.array_encode(['state'=>'error', 'compilesucc'=>0, 'runerr'=>1, 'msg'=>$error->getMessage(), 'score'=>0]);
} catch (Throwable $error) {
    // 运行环境故障只向网页报告未就绪，系统路径及异常细节保留在服务日志中。
    error_log($error->getMessage());
    http_response_code(503);
    echo '评测机未就绪。';
}
