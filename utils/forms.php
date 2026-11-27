<?php
/**
 * 管理网页表单和 AJAX 共用的会话令牌。bootstrap.php 验证 POST，写入入口调用 requirePost() 处理旧的 GET 链接。
 * 例：echo formTokenField();  // 放进 method="post" 的表单。
 */
/** 把普通文字转义为 HTML 文本或带引号的属性值；富文本题面另用 richText。 */
function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** 取当前会话的表单令牌，没有令牌时生成一个。 */
function formToken(): string
{
    return $_SESSION['form_token'] ??= bin2hex(random_bytes(32));
}

/** 返回隐藏的 form_token 输入框，供 POST 表单输出。 */
function formTokenField(): string
{
    return '<input type="hidden" name="form_token" value="'.formToken().'">';
}

/** 接受隐藏字段或 AJAX 请求头，令牌与当前会话不符时直接返回 403。 */
function verifyForm(): void
{
    $token = $_POST['form_token'] ?? $_SERVER['HTTP_X_COGS_TOKEN'] ?? null; // AJAX 请求头和表单字段共用会话令牌。
    if (!is_string($token) || !hash_equals(formToken(), $token)) {
        http_response_code(403); // 过期页面或其他会话的令牌不能提交。
        exit('表单已失效，请返回页面重新提交。');
    }
}

/** 写入端点先调用此函数。旧的 GET 链接显示确认表单，POST 才继续执行业务。 */
function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return; // 已由 bootstrap 校验令牌，可进入写入流程。
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET, POST', true, 405); // 外部调用方可从 Allow 响应头得知可用方法。
        exit;
    }
    // 旧书签仍可到达确认页。GET 预览和爬虫访问不会触发写操作。
    echo '<form method="post">'.formTokenField().'<button type="submit">确认执行此操作</button></form>';
    exit;
}
