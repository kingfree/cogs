<?php
// 发表或修改题目、文章的评论。修改时沿用原评论的对象并检查作者身份。
// 打开 problem/comment.php?pid=1 填写内容提交；同一请求只能选择一道题或一篇文章。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
$uid = (int)$_SESSION['ID'];
// 评论编号为空表示发表；已存在的评论必须由作者本人修改。
$cid = (int)($_POST['cid'] ?? 0);
$pid = (int)($_POST['pid'] ?? 0);
$aid = (int)($_POST['aid'] ?? 0);
// 提供评论号时以原帖作者和对象为准，避免误改别人的楼层。
if ($cid) {
    // 编辑原评论必须使用数据库保存的讨论对象，忽略表单中写的其他题号。
    $comment = Comment::byId($cid)[0] ?? null;
    if (!$comment || (int)$comment['uid'] !== $uid) { http_response_code(403); exit('只能修改自己的评论。'); }  // 作者身份按会话账号核对。
    // 用原评论的归属覆盖请求值，保留已经存在的讨论关系。
    $pid = (int)$comment['pid'];
    $aid = (int)$comment['aid'];
}
// 在写入前再次检查原题或原文章现在是否仍可阅读。
if (($pid && $aid) || !($pid ? Problem::forReader($pid) : Page::forReader($aid))) {
    http_response_code(404); exit('讨论对象不存在或未开放。');
}
// 去除首尾空白后判断正文是否真的有内容。
$detail = trim($_POST['detail'] ?? '');
// 评论按字节大小匹配数据库 TEXT 的容量，纯空白也不能发表。
if ($detail === '' || strlen($detail) > 65535) { http_response_code(400); exit('评论内容为空或过长。'); }
$share = (int)($pid && !empty($_POST['showcode']));  // 只有题目讨论能附上本人的最近提交。
// 只有题目评论可附上作者最近一次提交，文章评论没有代码可公开。
if ($cid) Comment::edit($detail, time(), $share, $cid, $uid);
elseif ($pid) Comment::onProblem($pid, $uid, $detail, time(), $share);
else Comment::onPage($aid, $uid, $detail, time(), 0);
i提示('发表评论成功！', 取路径('problem/comments.php?'.($pid ? 'pid='.$pid : 'aid='.$aid)));
