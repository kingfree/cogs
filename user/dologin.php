<?php
require_once("../include/stdhead.php");
$p=new DataAccess();
$uid = $_REQUEST['username'];
$pwd = $_REQUEST['pwdhash'];
if ($uid == "") $uid = $_COOKIE['User'];
if ($pwd == "") $pwd = $_COOKIE['cojs_login'];
$sql="select * from userinfo where usr='".$uid."'";
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
        $sql="insert into login(uid,ua,ip,ltime) values({$d['uid']},'".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."','{$_SERVER['REMOTE_ADDR']}',NOW())";
        $q->dosql($sql);
        if ($_REQUEST['savepwd']) {
            setcookie("cojs_login",$d['pwdhash'], time()+7776000);
            setcookie("User",$_POST['username'], time()+7776000);
        }
        if (!$_REQUEST['from'])
            $_REQUEST['from']=base64_encode("/".$SET['global_root']);
        i提示("用户 {$d['nickname']} 登录成功！", $_REQUEST['from']);
    } else
        i异常("密码错误，登录失败！");
}
?>
