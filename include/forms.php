<?php
// 浏览器表单与 AJAX 共用会话令牌。用 formTokenField() 输出隐藏表单域。
function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formToken(): string
{
    return $_SESSION['form_token'] ??= bin2hex(random_bytes(32));
}

function formTokenField(): string
{
    return '<input type="hidden" name="form_token" value="'.formToken().'">';
}

function verifyForm(): void
{
    $token = $_POST['form_token'] ?? $_SERVER['HTTP_X_COGS_TOKEN'] ?? null;
    if (!is_string($token) || !hash_equals(formToken(), $token)) {
        http_response_code(403);
        exit('表单已失效，请返回页面重新提交。');
    }
}

function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return;
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET, POST', true, 405);
        exit;
    }
    // 旧书签仍可到达确认页。GET 预览和爬虫访问不会触发写操作。
    echo '<form method="post">'.formTokenField().'<button type="submit">确认执行此操作</button></form>';
    exit;
}
