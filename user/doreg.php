<?php
require_once("../include/header.php");
gethead(8,"","");
过滤();
$_POST['usr']=htmlspecialchars($_POST['usr']);
$_POST['nickname']=htmlspecialchars($_POST['nickname']);
$_POST['realname']=htmlspecialchars($_POST['realname']);
$_POST['email']=htmlspecialchars($_POST['email']);
$_POST['user_style']=htmlspecialchars($_POST['user_style']);

if($_POST['VerifyCode'] != $_SESSION["IMGCODE"])
异常("验证码错误！",取路径("user/register.php?accept=1"));
$p=new DataAccess();
$LIB->cls_reg();
$rc=new RegisterCheck;
$sql="select * from userinfo where usr='".$_POST['usr']."'";
$cnt=$p->dosql($sql);
if ($cnt==0) {
    $sql="insert into userinfo(uid,usr,nickname,readforce,admin,regtime,pwdhash,pwdtipques,pwdtipanshash,memo,realname,email,gbelong) values (0, '{$_POST[usr]}','{$_POST[nickname]}','{$SET['reg_readforce']}',0, ". time() .",'". encode($_POST[pwd]) ."' ,'{$_POST[passwordtip]}' , '". encode($_POST[passwordtipans]) ."', '{$_POST[memo]}','{$_POST['realname']}','{$_POST['email']}','{$SET['reg_defgroup']}')";
    $p->dosql($sql);
    $sql="select * from userinfo where usr='".$_POST['usr']."'";
    $cnt2=$p->dosql($sql);
    if ($cnt2==1)
        $d=$p->rtnrlt(0);
    else
        异常("用户 {$_POST['usr']} 注册失败！",取路径("user/register.php?accept=1"));
    提示("用户 {$_POST['usr']} 注册成功，可以登录了！",取路径("index.php"));
} else
异常("用户 {$_POST['usr']} 已存在！",取路径("user/register.php?accept=1"));
?>
