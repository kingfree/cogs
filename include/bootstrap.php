<?php
/**
 * 普通网页从 require_once __DIR__.'/../include/header.php' 进入。这里准备配置、会话和表单，随后才由网页调用业务主体。
 * 下载和 JSON 页面也使用这套身份与令牌检查，但自行选择返回格式。
 */
require_once dirname(__DIR__).'/utils/forms.php';                     // HTML 转义、表单令牌和写操作确认。
set_exception_handler(static function (Throwable $error): void {
    // 独立页面可在本地把预期错误翻成专属响应。其余错误在这里收尾，底层诊断留在服务端。
    if ($error instanceof DomainException || $error instanceof InvalidArgumentException) {
        $status = (int)$error->getCode();                             // 主体可用 403、404 等标记用户可处理的问题。
        http_response_code($status >= 400 && $status < 500 ? $status : 400);
        echo h($error->getMessage());                                 // 用户输入可能出现在说明中，只回显文字。
        return;
    }
    $reference = bin2hex(random_bytes(8));                            // 同一编号把网页提示和服务器日志对应起来。
    error_log('[COGS '.$reference.'] '.get_class($error).': '.$error->getMessage().' in '.$error->getFile().':'.$error->getLine());
    http_response_code(500);
    echo '程序出错，请向管理员提供编号 '.h($reference).'。';            // 数据库和文件错误的细节仅由管理员查阅。
});
require_once __DIR__.'/env.inc.php';                                  // 读取站点数据库和本机数据目录。
require_once dirname(__DIR__).'/utils/database.php';                  // 所有主体共用 PDO 连接及参数绑定。
spl_autoload_register(static function ($name) {
    if (!preg_match('/^[A-Z][A-Za-z]*$/D', $name)) return;           // 只在业务层查找形如 ContestRound 的类名。
    $file = dirname(__DIR__).'/features/'.$name.'.php';               // 加一个业务文件即可按类名调用。
    if (is_file($file)) require_once $file;
});
date_default_timezone_set('Asia/Shanghai');                           // 新旧比赛都按站点时间解释赛程。
session_start(['use_strict_mode' => true, 'cookie_httponly' => true, 'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']); // HTTPS 登录凭据只经加密连接返回。
header('Content-Type: text/html; charset=UTF-8');                     // 附件或 API 入口稍后可选择自己的响应类型。

foreach ($_GET as $value) {                                           // 旧网址只接受单值查询参数。
    if (!is_string($value)) throw new InvalidArgumentException('查询参数格式错误。');
}
foreach ($_POST as $field => $value) {
    if (is_string($value)) continue;                                  // 普通表单字段直接交给对应指令。
    $multiple = in_array($field, ['cons','cate','doit','doall'], true); // 这四个控件允许一次提交多个选择。
    if (!$multiple || !is_array($value) || count(array_filter($value, 'is_string')) !== count($value)) {
        throw new InvalidArgumentException('表单参数格式错误。');        // 拒绝嵌套数组，后续无需重复猜测字段形状。
    }
}
require_once dirname(__DIR__).'/utils/paths.php';                      // 站内 URL 与磁盘位置分别计算。
require_once dirname(__DIR__).'/utils/rich-text.php';                  // 重建可安全显示的文章和题面元素。
require_once dirname(__DIR__).'/utils/files.php';                      // 在附件和备份列表中显示文件大小。
require_once dirname(__DIR__).'/utils/pagination.php';                 // 同一页码用于数据库偏移和翻页链接。
require_once __DIR__.'/format.php';                                   // 旧讨论和公告的显示约定。
require_once __DIR__.'/notice.php';                                   // 表单执行后的提示与站内返回链接。
require_once __DIR__.'/results.php';                                  // 测试点字母与中文判定。
require_once __DIR__.'/gravatar.php';                                 // 个人和榜单头像。
require_once __DIR__.'/grader-protocol.php';                          // 网站与评测机共用语言和请求格式。
require_once __DIR__.'/editor.inc.php';                               // 题面及文章编辑器。
require_once __DIR__.'/string.inc.php';                               // 页面展示的语言和评测模式名称。

$requestStarted = microtime(true);                                    // 页脚用本次起点计时。
$SET = array_column(Setting::all(), 'value', 'name');                 // 管理后台保存的设置按名称取值。
$SET['cur'] = $_SERVER['SCRIPT_FILENAME'];                            // 当前页面是所有相对链接的起点。
$SET['global_root'] = trim($cfg['dir_root'], '/');
if ($SET['global_root'] !== '') $SET['global_root'] .= '/';           // 子目录部署保留一个尾斜杠。
$SET['base'] = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/'.$SET['global_root'];
$SET['URI'] = base64_encode($_SERVER['REQUEST_URI']);                 // 完成表单后可回到原页面及筛选条件。
$cfg['Version'] = '2.8.2.1';                                         // 页脚沿用站点版本。
$pri = Privilege::NAMES;                                             // 旧权限模板按编号查中文名称。

if (empty($_SESSION['ID'])) Login::restore();                         // 首次请求可从有效的长期令牌恢复会话。
else Login::loadUser($_SESSION['ID']);                                // 已登录用户每次都读取最新权限及密码版本。
$_SESSION += ['ID'=>0, 'readforce'=>0, 'group'=>0, 'style'=>0, 'admin'=>0,
    'user_style'=>$SET['user_style'], 'navbar'=>0];                    // 游客获得模板所需的只读默认资料。
if ($_SERVER['REQUEST_METHOD'] === 'POST') verifyForm();              // 任何网页写入前都校验本会话令牌。
