<?php
// 处理 mail/index.php 写信表单中的 toid、title 和 msg，以当前登录账号为发件人。
require_once(__DIR__.'/../include/header.php');
gethead(8,"sess","");
requirePost();
// 收件人由表单选择，发件人始终使用服务器保存的登录身份。
$recipient = (int)($_POST['toid'] ?? 0);
$title = trim($_POST['title'] ?? ''); // 邮件列表依赖可见标题，拒绝全空格和超过库内长度的标题。
$body = $_POST['msg'] ?? ''; // 正文允许缩进与换行，检验非空时单独取 trim 后的结果。
$return = 取路径('mail/index.php'); // 表单错误返回写信入口，正文不在地址栏传递。
if ($recipient < 1) 异常('收件人错误！', $return);
if ($title === '' || mb_strlen($title) > 200) 异常('标题错误！', $return);
if (trim($body) === '' || strlen($body) > 65535) 异常('信件内容错误！', $return); // 正文保留用户的缩进与换行。
if (!Message::send((int)$_SESSION['ID'], $recipient, $title, $body)) 异常('收件人错误！', $return); // 发送与核对收件人合并执行，无此账号时返回原表单。
提示('发送邮件成功！', $return);
