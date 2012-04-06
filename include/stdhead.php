<?php
//set_time_limit(0);
error_reporting(0);
// write log
require_once("stdlib.php");
$LIB->cls_getsettings();
if(!isset($_SESSION['ID'])) {
    $_SESSION['readforce']=0;
    $_SESSION['ID']=0;
}

function gethead($head,$check,$title) {
    global $SET,$cfg,$LIB;
    /*if(!$_SESSION['ID'])
        if($_COOKIE['User']) if($_COOKIE['cojs_login'])
            header("location: ".路径("user/dologin.php"));*/
?>
<!DOCTYPE HTML>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel=stylesheet href="<?=路径("style/".$SET['style_profile'])?>"><?php $LIB->tradsimp(); ?>
<link rel=stylesheet type="text/css" href="<?=路径("style/icon.css")?>" />
<script type="text/javascript" src="<?=路径("include/jquery.js")?>"></script>
<script type="text/javascript" src="<?=路径("include/sortTable.js")?>"></script>
<title><?php echo $title." - ".$SET['global_sitename'] ?></title>
</head>
<body>
<div id="alltext">
<?
if ($check=="sess") {
    if (!$_SESSION['ID'])
        echo '<script>document.location="../error.php?id=1"</script>';
} else if ($check=="admin") {
    if (!($_SESSION['admin']>0))
        echo '<script>document.location="../error.php?id=7"</script>';
} else if ($check=="advadmin") {
    if ($_SESSION['admin']!=2)
        echo '<script>document.location="../error.php?id=7"</script>';
} else if ($check=="verfy") {
    if ($_SESSION['admin']!=-1)
        echo '<script>document.location="../error.php?id=2"</script>';
}
    if ($head==1) {
        $useracc=new DataAccess();
?>
<table id="globalbar">
  <tr>
    <td rowspan="2"><?php showuser($_SESSION['ID'], $useracc) ?></td>
    <td><div id="global_head"><?=输出文本($SET['global_head'])?></div></td>
  </tr>
  <tr>
    <td><?php include($SET['base']."include/stdbar.php") ?></td>
  </tr>
</table>

<div id="body">
<?php
    }
}
?>
