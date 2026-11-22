<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
Message::markRead((int)($_POST['mid'] ?? 0), $_SESSION['ID']);
