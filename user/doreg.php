<?php
require_once("../include/header.php");
gethead(8,"","");
过滤();
$_POST['usr']=htmlspecialchars($_POST['usr']);
$_POST['nickname']=htmlspecialchars($_POST['nickname']);
$_POST['realname']=htmlspecialchars($_POST['realname']);
$_POST['email']=htmlspecialchars($_POST['email']);
$_POST['user_style']=htmlspecialchars($_POST['user_style']);
$_POST['memo']=htmlspecialchars($_POST['memo']);

$regpage=取路径("user/register.php?accept=1");

if($_POST['VerifyCode'] != $_SESSION["IMGCODE"])
异常("验证码错误，请一律小写！", $regpage);
if(!preg_match('/([a-z0-9][_a-z0-9]{3,23})/', $_POST['usr']))
异常("用户名长度必须在[4,24]中且只能使用英文字母、数字以及_，并且首字符必须为字母或数字。", $regpage);
$p=new DataAccess();
$LIB->cls_reg();
$rc=new RegisterCheck;
$sql="select * from userinfo where usr='".$_POST['usr']."'";
$cnt=$p->dosql($sql);
if ($cnt==0) {
if(!preg_match('/(.{4,24})/', $_POST['pwd']))
异常("密码长度必须在[4,24]中。", $regpage);
if($_POST['pwd'] != $_POST['repwd'])
异常("重复输入密码必须和密码相同。", $regpage);
if(!preg_match('/(\S{2,20})/', $_POST['nickname']))
异常("昵称长度必须在[2,20]中。", $regpage);
if(!preg_match('/(\S*@\S*\.\S*)/', $_POST['email']))
异常("电子邮箱格式不正确。", $regpage);
if(!preg_match('/(\S{2,8})/', $_POST['realname']))
异常("真实姓名长度必须在[2,8]中，应该是汉字。", $regpage);
if(!preg_match('/(.{2,64})/', $_POST['passwordtip']))
异常("提示问题长度必须在[2,64]中。", $regpage);
if(!preg_match('/(.{2,64})/', $_POST['passwordtipans']))
异常("提示问题答案长度必须在[2,64]中。", $regpage);
    $sql="insert into userinfo(uid,usr,nickname,readforce,admin,regtime,pwdhash,pwdtipques,pwdtipanshash,memo,realname,email,gbelong,user_style,style) values (0, '{$_POST[usr]}','{$_POST[nickname]}','{$SET['reg_readforce']}',0, ". time() .",'". encode($_POST[pwd]) ."' ,'{$_POST[passwordtip]}' , '". encode($_POST[passwordtipans]) ."', '{$_POST[memo]}','{$_POST['realname']}','{$_POST['email']}','{$SET['reg_defgroup']}','{$SET['user_style']}', 0)";
    $p->dosql($sql);
    $sql="select * from userinfo where usr='".$_POST['usr']."'";
    $cnt2=$p->dosql($sql);
    if ($cnt2==1)
        $d=$p->rtnrlt(0);
    else
        异常("用户 {$_POST['usr']} 注册失败！",取路径("user/register.php?accept=1"));
    $tm = time()+7776000;
    setcookie("cogs_usr", $_POST[usr], $tm, "/");
    setcookie("cogs_pwd_hash",encode($_POST[pwd]), $tm, "/");
    提示("用户 {$_POST['usr']} 注册成功！生成头像缓存需要一定时间等耐心等待……<p>之后你可以更改你的个人信息看看。",取路径("user/panel.php"));
} else
异常("用户 {$_POST['usr']} 已存在！",取路径("user/register.php?accept=1"));
?>
