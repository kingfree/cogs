<?php
// 在题面给一道题添加标签。登录后从 problem/problem.php?pid=1 的分类表单提交名称和说明。
require_once __DIR__.'/../include/header.php';
gethead(8, '普通用户', '');
requirePost();
// 只能给自己能够打开的题面加标签。
$pid = (int)($_POST['pid'] ?? 0);
$back = 取路径("problem/problem.php?pid=$pid");
if (!Problem::forReader($pid)) { http_response_code(404); exit('题目不存在或未开放。'); }  // 不可读取的题目也不能通过标签表单探测。
$name = trim($_POST['cname'] ?? '');     // 按输入的文字检索分类，显示时才做 HTML 转义。
$memo = trim($_POST['memo'] ?? '');
if ($name === '' || mb_strlen($name) > 24) 异常('分类名须为 1 至 24 个字符。', $back);

// 追加说明与建立关联一起保存，重复关联另在提示中说明。
[$categoryId, $alreadyTagged] = Category::labelProblem($pid, $name, $memo);
$label = h($name);                       // 提示组件支持富文本，分类名必须作为普通文字嵌入。
$message = "添加题目 $pid 分类 “{$categoryId}. {$label}” 成功！";
// 同一题再贴同一个标签时仍提示说明已经追加。
if ($alreadyTagged) $message .= '这道题已经有此分类。';
提示($message, $back);
