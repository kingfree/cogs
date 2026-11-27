<?php
/**
 * 所有普通网页从这里加载站点环境，再调用 gethead 检查入口权限。下载文件或 JSON 响应必须先检查权限，再输出响应头。
 * 例：gethead(0, '查看数据', '');  // 只检查权限，不输出页面。
 */
require_once __DIR__.'/bootstrap.php';

/** $check 是所需权限；$head 为 0 时仅校验访问，为 8 时输出无导航的提示页页头。 */
function gethead($head,$check,$title,$userid=0) {
    global $SET;
    $aliases = ['sess'=>'普通用户', 'admin'=>'管理用户', 'advadmin'=>'超级用户'];
    $required = $aliases[$check] ?? $check; // 老页面传入的权限简称对应同一份站点授权。
    if (!Privilege::allows($required)) {
        http_response_code(403); // 在输出页头之前拒绝，下载或 JSON 入口也会收到正确状态。
        exit('没有权限 '.h($required).'！');
    }
    if ($head <= 0) return; // 只做访问检查的请求继续生成自己的响应。
    $user_style = $_SESSION['user_style'] ?: $SET['user_style']; // 个人主题优先，游客使用站点默认主题。
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="cogs-token" content="<?=formToken()?>">


<link rel="Shortcut Icon" href="<?=路径("style/cogs.png")?>" />
<link rel=stylesheet type="text/css" href="/Bootstrap/css/<?=h($user_style)?>.min.css" />
<link rel=stylesheet href="<?=h(路径("style/{$SET['style_profile']}"))?>" />
<?php 背景图片($userid ? $userid : $_SESSION['ID']);?>
<?php require_once __DIR__.'/tradsimp.inc.php'; ?>
<script type="text/javascript" src="/jQuery/jquery-1.8.0.min.js"></script>
<script type="text/javascript" src="<?=路径("include/sortTable.js")?>"></script>
<script type="text/javascript" src="/Bootstrap/js/bootstrap.min.js"></script>
<!--[if IE 6]>
<link href="/Bootstrap-IE6/ie6.min.css" type="text/css" rel="stylesheet">
<![endif]-->
<title><?php echo h($title)." - ".h($SET['global_sitename']) ?></title>
</head>
<body>
<div id="alltext">
<?php
require_once __DIR__."/navigation.php";
if($head != 8) {
    Navigation(str_contains($_SERVER['HTTP_USER_AGENT'] ?? '', 'MSIE')); // 旧 IE 展示相同入口的简化菜单。
}
?>

<div id="body" class='container-fluid'>
<?php
}
