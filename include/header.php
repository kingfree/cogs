<?php
require_once("lib.php");
$LIB->cls_getsettings();
   global $SET,$cfg,$LIB;
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
                $q=new DataAccess();
                $sql="UPDATE `userinfo` SET `lastip` = '{$_SERVER['REMOTE_ADDR']}' WHERE `uid` ={$d['uid']}";
                $q->dosql($sql);
                $sql="insert into login(uid,ua,ip,ltime,version) values({$d['uid']},'".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."','{$_SERVER['REMOTE_ADDR']}',NOW(),'".mysql_real_escape_string($cfg['dir_root'])."')";
                if($SET['login_log']) $q->dosql($sql);
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
<meta name="author" content="CmYkRgB123, BYVoid; 王者自由, Kingfree" />
<meta name="description" content="CmYkRgB123 Online Grading System，简称 COGS ，又称 COJS。是一款 OI 在线评测系统，基于 LAMP 技术，文件输入输出，支持评测插件和提交答案。" />
<meta name="keywords" content="COGS, COJS, OJ, OI, NOI, ACM/ICPC" />
<meta name="generator" content="Vim" />
<meta name="revised" content="<?=$cfg['Version']?>" />
<meta name="others" content="" />

<link rel="Shortcut Icon" href="<?=路径("style/cogs.png")?>" />
<link rel=stylesheet href="<?=路径("style/{$SET['style_profile']}")?>" />
<?背景图片($userid ? $userid : $_SESSION['ID']);?>
<?php $LIB->tradsimp(); ?>
<link rel=stylesheet type="text/css" href="/Bootstrap/css/<?=$user_style?>.min.css" />
<script type="text/javascript" src="/jQuery/jquery-1.8.0.min.js"></script>
<script type="text/javascript" src="<?=路径("include/sortTable.js")?>"></script>
<script type="text/javascript" src="/Bootstrap/js/bootstrap.min.js"></script>
<? if($head == 7) { ?>
<script type="text/javascript" src="/UEditor/editor_config.js"></script>
<script type="text/javascript" src="/UEditor/editor_all_min.js"></script>
<link rel="stylesheet" href="/UEditor/themes/default/ueditor.css">
<? } ?>
<!--[if IE 6]>
<link href="/Bootstrap-IE6/ie6.min.css" type="text/css" rel="stylesheet">
<![endif]-->
<title><?php echo $title." - ".$SET['global_sitename'] ?></title>
</head>
<body>
<div id="alltext">
<?
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

