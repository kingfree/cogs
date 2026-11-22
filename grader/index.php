<?php
require_once dirname(__FILE__)."/stdlib.php";

if (!isset($_POST['query'])) {
    require dirname(__FILE__)."/welcome.php";
    return;
}
if (!graderSignatureValid($_POST, getenv('COGS_GRADER_KEY'), time())) {
    http_response_code(403);
    exit;
}
// 编译器和选手程序无需继承调度密钥。
putenv('COGS_GRADER_KEY');
unset($_ENV['COGS_GRADER_KEY'], $_SERVER['COGS_GRADER_KEY']);
$query = array_decode($_POST['query']);
if (!graderRequestAllowed($query)) {
    http_response_code(400);
    exit;
}
require dirname(__FILE__)."/grade.php";
