<?php
// 从 contest/judge.php 提交 csid 和 judger，返回感叹号分隔的得分、判定、耗时、内存。
require_once __DIR__.'/../include/header.php';
gethead(0, '查看比赛', ''); // 只有可评测比赛的用户才能触发重判。
requirePost(); // 批量评测页逐份请求，每份都要通过表单令牌检查。
require_once __DIR__.'/../include/socket.php';
try {
    $result = ContestEntry::grade((int)($_POST['csid'] ?? 0), (int)($_POST['judger'] ?? 0), $SET['dir_competition']);
    echo $result === null ? 'No Grader!Please Wait and Retry.' : implode('!', $result); // 保留旧版批量评测页的响应格式。
} catch (OutOfBoundsException $error) {
    throw new DomainException('交卷不存在。', 404); // 管理队列中的交卷可能已被移除。
} catch (UnexpectedValueException $error) {
    throw new DomainException($error->getMessage(), 409); // 选手已重交，旧分数没有覆盖新文件。
} catch (DomainException $error) {
    throw new DomainException($error->getMessage(), 422); // 题目中不受支持的测试点数可由管理员改正。
} catch (RuntimeException $error) {
    error_log($error->getMessage()); // 文件位置或评测机故障原因只记入站点日志。
    http_response_code(503); exit('评测服务暂时不可用，请稍后重试。');
}
