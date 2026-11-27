<?php
/**
 * 所有普通网页从这里加载站点环境，再调用 gethead 检查入口权限。下载文件或 JSON 响应必须先检查权限，再输出响应头。
 * 例：gethead(0, '查看数据', '');  // 只检查权限，不输出页面。
 */
require_once __DIR__.'/bootstrap.php';

/** $check 是所需权限；$head 为 0 时仅校验访问，为 8 时输出无导航的提示页页头。 */
function gethead($head,$check,$title,$userid=0) {
    global $SET, $cfg;
    $aliases = ['sess'=>'普通用户', 'admin'=>'管理用户', 'advadmin'=>'超级用户'];
    $required = $aliases[$check] ?? $check;
    if (!Privilege::allows($required)) {
        http_response_code(403);
        exit('没有权限 '.h($required).'！');
    }
   $user_style = $_SESSION['user_style']?$_SESSION['user_style']:$SET["user_style"];
   if($head > 0) {
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="cogs-token" content="<?=formToken()?>">
<!--<meta name="author" content="CmYkRgB123, BYVoid; 王者自由, Kingfree" />
<meta name="description" content="CmYkRgB123 Online Grading System，简称 COGS ，又称 COJS。是一款 OI 在线评测系统，基于 LAMP 技术，文件输入输出，支持评测插件和提交答案。" />
<meta name="keywords" content="COGS, COJS, OJ, OI, NOI, ACM/ICPC" />
<meta name="generator" content="Vim" />
<meta name="revised" content="<?=$cfg['Version']?>" />
<meta name="others" content="" />-->

<link rel="Shortcut Icon" href="<?=路径("style/cogs.png")?>" />
<link rel=stylesheet type="text/css" href="/Bootstrap/css/<?=h($user_style)?>.min.css" />
<link rel=stylesheet href="<?=路径("style/{$SET['style_profile']}")?>" />
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
    if(strpos(($_SERVER['HTTP_USER_AGENT'] ?? ''), 'MSIE') === false) Navigation();
    else Navigation_IE();
}
?>

<div id="body" class='container-fluid'>
<?php
    }
}
