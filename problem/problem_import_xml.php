<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '修改题目', '');
requirePost();
if (!is_uploaded_file($_FILES['fps']['tmp_name'] ?? '')) { http_response_code(400); exit('请选择 FPS 文件。'); }
try {
    $ids = Fps::import($_FILES['fps']['tmp_name'], (int)$_SESSION['ID']);
    i提示('已导入 '.count($ids).' 道题目。', 取路径('problem/index.php'));
} catch (DomainException $error) { http_response_code(400); exit(h($error->getMessage())); }
