<?php
error_reporting(E_ALL);
require_once("lib.php");
$LIB->cls_getsettings();
if(!($_SESSION['ID'])) {
    if($_COOKIE['cogs_usr'] && $_COOKIE['cogs_pwd_hash']) {
        $p=new DataAccess();
        $usr = $_COOKIE['cogs_usr'];
        $pwd = $_COOKIE['cogs_pwd_hash'];
        $sql="select * from userinfo where usr='$usr' AND pwdhash='$pwd' limit 1";
        $cnt=$p->dosql($sql);
        if($cnt) {
            $d=$p->rtnrlt(0);
            if($pwd==$d['pwdhash']) {
                $LIB->get_userinfo($d['uid']);
            }
        }
    }
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
<link rel="Shortcut Icon" href="<?=路径("style/cogs.png")?>" />
<link rel=stylesheet href="<?=路径("style/{$SET['style_profile']}")?>" />
<?背景图片($userid ? $userid : $_SESSION['ID']);?>
<?php $LIB->tradsimp(); ?>
<link rel=stylesheet type="text/css" href="/Bootstrap/css/<?=$user_style?>.min.css" />
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
<?
$p=new DataAccess();
if($check=="sess") $check="普通用户";
else if($check=="admin") $check="管理用户";
else if($check=="advadmin") $check="超级用户";
if(!有此权限($check)) 异常("没有权限 $check !");

include(路径("include/navigation.php"));
if($head != 8) {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') == false) Navigation($p);
    else Navigation_IE($p);
}
?>

<div id="body" class='container-fluid'>
<?php
    }
}
?>

