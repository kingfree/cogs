<?php
date_default_timezone_set('PRC');
error_reporting(E_ERROR);
require_once("lib.php");
$LIB->cls_getsettings();
   global $SET,$cfg,$LIB;
require_once dirname(__FILE__)."/login-session.php";
if(!isset($_SESSION['ID'])) {
    restoreLogin();
    if(!($_SESSION['ID'])) {
        $_SESSION['readforce']=0;
        $_SESSION['ID']=0;
        $_SESSION['user_style']=$SET["user_style"];
        $_SESSION['navbar']=0;
    }
}

function gethead($head,$check,$title,$userid=0) {
   global $SET,$cfg,$LIB;
   $user_style = $_SESSION['user_style']?$_SESSION['user_style']:$SET["user_style"];
   if($head > 0) {
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<!--<meta name="author" content="CmYkRgB123, BYVoid; 王者自由, Kingfree" />
<meta name="description" content="CmYkRgB123 Online Grading System，简称 COGS ，又称 COJS。是一款 OI 在线评测系统，基于 LAMP 技术，文件输入输出，支持评测插件和提交答案。" />
<meta name="keywords" content="COGS, COJS, OJ, OI, NOI, ACM/ICPC" />
<meta name="generator" content="Vim" />
<meta name="revised" content="<?=$cfg['Version']?>" />
<meta name="others" content="" />-->

<link rel="Shortcut Icon" href="<?=路径("style/cogs.png")?>" />
<link rel=stylesheet type="text/css" href="/Bootstrap/css/<?=$user_style?>.min.css" />
<link rel=stylesheet href="<?=路径("style/{$SET['style_profile']}")?>" />
<?php 背景图片($userid ? $userid : $_SESSION['ID']);?>
<?php $LIB->tradsimp(); ?>
<script type="text/javascript" src="/jQuery/jquery-1.8.0.min.js"></script>
<script type="text/javascript" src="<?=路径("include/sortTable.js")?>"></script>
<script type="text/javascript" src="/Bootstrap/js/bootstrap.min.js"></script>
<!--[if IE 6]>
<link href="/Bootstrap-IE6/ie6.min.css" type="text/css" rel="stylesheet">
<![endif]-->
<title><?php echo $title." - ".$SET['global_sitename'] ?></title>
</head>
<body>
<div id="alltext">
<?php
if($check=="sess") $check="普通用户";
else if($check=="admin") $check="管理用户";
else if($check=="advadmin") $check="超级用户";
if(!有此权限($check)) 异常("没有权限 $check !");

$pi=new DataAccess();
require_once(路径("include/navigation.php"));
if($head != 8) {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') == false) Navigation($pi);
    else Navigation_IE($pi);
}
?>

<div id="body" class='container-fluid'>
<?php
    }
}
?>

