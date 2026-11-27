<?php
// 收件箱弹窗由 JavaScript POST 邮件编号 mid 和表单令牌；读取者身份取自会话。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
Message::markRead((int)($_POST['mid'] ?? 0), $_SESSION['ID']);
