<?php
// 网页共用的请求入口。加载配置和登录身份后调用具体业务，例如 Problem::byId(1)。
require_once __DIR__.'/env.inc.php';
require_once __DIR__.'/db.php';
spl_autoload_register(static function ($name) {
    if (!preg_match('/^[A-Z][A-Za-z]*$/D', $name)) return;
    $file = dirname(__DIR__).'/features/'.$name.'.php';
    if (is_file($file)) require_once $file;
});
date_default_timezone_set('Asia/Shanghai');
session_start(['use_strict_mode' => true, 'cookie_httponly' => true, 'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
header('Content-Type: text/html; charset=UTF-8');
foreach ($_GET as $value) if (!is_string($value)) { http_response_code(400); exit('查询参数格式错误。'); }
foreach ($_POST as $field=>$value) {
    if (is_string($value)) continue;
    if (!in_array($field, ['cons','cate','doit','doall'], true) || !is_array($value) ||
        count(array_filter($value, 'is_string')) !== count($value)) {
        http_response_code(400); exit('表单参数格式错误。');
    }
}
require_once __DIR__.'/paths.php';
require_once __DIR__.'/format.php';
require_once __DIR__.'/rich-text.php';
require_once __DIR__.'/files.php';
require_once __DIR__.'/pagination.php';
require_once __DIR__.'/notice.php';
require_once __DIR__.'/results.php';
require_once __DIR__.'/clock.php';
require_once __DIR__.'/gravatar.php';
require_once __DIR__.'/grader-protocol.php';
require_once __DIR__.'/forms.php';
require_once __DIR__.'/editor.inc.php';
require_once __DIR__.'/string.inc.php';
$time_Ls = gettime();
$SET = array_column(Setting::all(), 'value', 'name');
$SET['cur'] = $_SERVER['SCRIPT_FILENAME'];
$SET['global_root'] = trim($cfg['dir_root'], '/');
if ($SET['global_root'] !== '') $SET['global_root'] .= '/';
$SET['base'] = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/'.$SET['global_root'];
$SET['URI'] = base64_encode($_SERVER['REQUEST_URI']);
$cfg['Version'] = '2.8.2.1';
$pri = Privilege::NAMES;
if (empty($_SESSION['ID'])) Login::restore();
else Login::loadUser($_SESSION['ID']);
$_SESSION += ['ID' => 0, 'readforce' => 0, 'group' => 0, 'style'=>0, 'admin'=>0, 'user_style' => $SET['user_style'], 'navbar' => 0];
if ($_SERVER['REQUEST_METHOD'] === 'POST') verifyForm();
