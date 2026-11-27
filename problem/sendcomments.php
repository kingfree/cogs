<?php
// 发表或修改题目、文章的评论。修改时沿用原评论的对象并检查作者身份。
// 打开 problem/comment.php?pid=1 填写内容提交；同一请求只能选择一道题或一篇文章。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
$uid = (int)$_SESSION['ID'];
$cid = (int)($_POST['cid'] ?? 0);
$pid = (int)($_POST['pid'] ?? 0);
$aid = (int)($_POST['aid'] ?? 0);
if ($cid) {
    $comment = Comment::byId($cid)[0] ?? null;
    if (!$comment || (int)$comment['uid'] !== $uid) { http_response_code(403); exit('只能修改自己的评论。'); }
    $pid = (int)$comment['pid'];
    $aid = (int)$comment['aid'];
}
if (($pid && $aid) || !($pid ? Problem::forReader($pid) : Page::forReader($aid))) {
    http_response_code(404); exit('讨论对象不存在或未开放。');
}
$detail = trim($_POST['detail'] ?? '');
if ($detail === '' || strlen($detail) > 65535) { http_response_code(400); exit('评论内容为空或过长。'); }
$share = (int)($pid && !empty($_POST['showcode']));
if ($cid) Comment::edit($detail, time(), $share, $cid, $uid);
elseif ($pid) Comment::onProblem($pid, $uid, $detail, time(), $share);
else Comment::onPage($aid, $uid, $detail, time(), 0);
i提示('发表评论成功！', 取路径('problem/comments.php?'.($pid ? 'pid='.$pid : 'aid='.$aid)));
