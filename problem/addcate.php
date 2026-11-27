<?php
// 在题面给一道题添加标签。登录后从 problem/problem.php?pid=1 的分类表单提交名称和说明。
require_once __DIR__.'/../include/header.php';
gethead(8, '普通用户', '');
requirePost();
$pid = (int)($_POST['pid'] ?? 0);
$back = 取路径("problem/problem.php?pid=$pid");
if (!Problem::forReader($pid)) { http_response_code(404); exit('题目不存在或未开放。'); }
$name = trim($_POST['cname'] ?? '');     // 按输入的文字检索分类，显示时才做 HTML 转义。
$memo = trim($_POST['memo'] ?? '');
if ($name === '' || mb_strlen($name) > 24) 异常('分类名须为 1 至 24 个字符。', $back);

$pattern = '%'.$name.'%';                // 沿用题面按名称或说明匹配相似分类的规则。
$category = Category::matching($pattern, $pattern)[0] ?? null;
$alreadyTagged = false;
if ($category) {
    $categoryId = (int)$category['caid'];
    Category::updateMemo(trim($category['memo'].' '.$memo), $categoryId);
    $alreadyTagged = (bool)Category::hasProblem($pid, $categoryId);
} else {
    $categoryId = Category::create($name, $memo);
}
if (!$alreadyTagged) Category::attach($pid, $categoryId);

$label = h($name);                       // 提示组件支持富文本，分类名必须作为普通文字嵌入。
$message = "添加题目 $pid 分类 “{$categoryId}. {$label}” 成功！";
if ($alreadyTagged) $message .= '这道题已经有此分类。';
提示($message, $back);
