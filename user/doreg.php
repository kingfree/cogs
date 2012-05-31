<?php
require_once("../include/stdhead.php");
gethead(8,"","");
$p=new DataAccess();
$LIB->cls_reg();
$rc=new RegisterCheck;
if (!$rc->check_reg($_POST,$_SESSION["IMGCODE"]))
    异常("注册信息有误，请重新填写！",取路径("user/register.php"));
$sql="select * from userinfo where usr='".$_POST['usr']."'";
$cnt=$p->dosql($sql);
if ($cnt==0) {
	$sql="insert into userinfo(uid,usr,nickname,readforce,admin,regtime,pwdhash,pwdtipques,pwdtipanshash,memo,realname,email) values (0, '{$_POST[usr]}','{$_POST[nickname]}',2,0, ". time() .",'". encode($_POST[pwd]) ."' ,'{$_POST[passwordtip]}' , '". encode($_POST[passwordtipans]) ."', '{$_POST[memo]}',". ",'{$_POST['realname']}','{$_POST['email']}')";
	$p->dosql($sql);
	$sql="select * from userinfo where usr='".$_POST['usr']."'";
	$cnt2=$p->dosql($sql);
	if ($cnt2==1)
		$d=$p->rtnrlt(0);
	else
        异常("用户 {$_POST['usr']} 注册失败！",取路径("user/register.php"));
    提示("用户 {$_POST['usr']} 注册成功，可以登录了！",取路径("index.php"));
} else
    异常("用户 {$_POST['usr']} 已存在！",取路径("user/register.php"));
?>
