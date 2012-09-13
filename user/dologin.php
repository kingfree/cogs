<?php
require_once("../include/header.php");
$p=new DataAccess();
$usr = $_REQUEST['username'];
$pwd = $_REQUEST['pwdhash'];
if(!$usr) $usr = $_COOKIE['cogs_usr'];
if(!$pwd) $pwd = $_COOKIE['cogs_pwd_hash'];
$sql="select * from userinfo where usr='$usr'";
$cnt=$p->dosql($sql);
if ($cnt==0)
    i异常("用户不存在！");
else {
    $d=$p->rtnrlt(0);
    if ($pwd==$d['pwdhash'] || (encode($_REQUEST['password'])==$d['pwdhash'])) {
        $LIB->get_userinfo($d['uid']);
        $q=new DataAccess();
        $sql="UPDATE `userinfo` SET `lastip` = '{$_SERVER['REMOTE_ADDR']}' WHERE `uid` ={$d['uid']}";
        $q->dosql($sql);
        $sql="insert into login(uid,ua,ip,ltime,version) values({$d['uid']},'".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."','{$_SERVER['REMOTE_ADDR']}',NOW(),'".mysql_real_escape_string($cfg['dir_root'])."')";
        if($SET['login_log']) $q->dosql($sql);
        if($_REQUEST['savepwd']) {
            $tm = time()+7776000;
            setcookie("cogs_usr", $usr, $tm, "/");
            setcookie("cogs_pwd_hash",$d['pwdhash'], $tm, "/");
        }
        if (!$_REQUEST['from'])
            $_REQUEST['from']=base64_encode("/".$SET['global_root']);
        i提示("用户 {$d['nickname']} 登录成功！{$_REQUEST['savepwd']}", $_REQUEST['from']);
    } else
        i异常("密码错误，登录失败！");
}
?>
