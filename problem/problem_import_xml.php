<?php
// 接收 FPS XML 上传，以当前登录用户为作者导入题目；导入规则在 Fps::import。
// 从 problem/problem_import.php 选择文件提交，表单文件字段名为 fps。
require_once __DIR__.'/../include/header.php';
gethead(0, '修改题目', '');
requirePost();
// 只有本次 HTTP 上传的文件可以交给导入器。
if (!is_uploaded_file($_FILES['fps']['tmp_name'] ?? '')) { http_response_code(400); exit('请选择 FPS 文件。'); }
try {
    $ids = Fps::import($_FILES['fps']['tmp_name'], (int)$_SESSION['ID']);  // 新题归本次上传的管理员账号。
    // 导入器会跳过旧题，完成提示只统计实际新增的题目。
    i提示('已导入 '.count($ids).' 道题目。', 取路径('problem/index.php'));
} catch (DomainException $error) { http_response_code(400); exit(h($error->getMessage())); }  // 无效 XML 或未配对的测试点可修改题包后重传。
