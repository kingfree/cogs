<?php
require_once("../include/header.php");
gethead(8,"","");

$_POST['usr']=htmlspecialchars($_POST['usr']);
$_POST['nickname']=htmlspecialchars($_POST['nickname']);
$_POST['realname']=htmlspecialchars($_POST['realname']);
$_POST['email']=htmlspecialchars($_POST['email']);
$_POST['user_style']=htmlspecialchars($_POST['user_style']);
$_POST['memo']=htmlspecialchars($_POST['memo']);

$regpage=取路径("user/register.php?accept=1");

if($_POST['VerifyCode'] != $_SESSION["IMGCODE"])
异常("验证码错误，请一律小写！", $regpage);
if(!preg_match('/^[a-z0-9_]{1,24}$/iD', $_POST['usr']))
异常("用户名长度必须在[1,24]中且只能使用英文字母、数字以及_。", $regpage);
$p=new DataAccess();
$LIB->cls_reg();
$rc=new RegisterCheck;
$cnt = $p->dosql("select uid from userinfo where usr=?", array($_POST['usr']));
if ($cnt==0) {
if(!preg_match('/(.{0,24})/', $_POST['pwd']))
异常("密码长度必须在[0,24]中。", $regpage);
if($_POST['pwd'] != $_POST['repwd'])
异常("重复输入密码必须和密码相同。", $regpage);
if(!preg_match('/(\S{1,20})/', $_POST['nickname']))
异常("昵称长度必须在[2,20]中。", $regpage);
if(!preg_match('/(\S*@\S*\.\S*)/', $_POST['email']))
异常("电子邮箱格式不正确。", $regpage);
if(!preg_match('/(\S{0,8})/', $_POST['realname']))
异常("真实姓名长度必须在[0,8]中，应该是汉字。", $regpage);
if(!preg_match('/(.{0,64})/', $_POST['passwordtip']))
异常("提示问题长度必须在[0,64]中。", $regpage);
if(!preg_match('/(.{0,64})/', $_POST['passwordtipans']))
异常("提示问题答案长度必须在[0,64]中。", $regpage);
    $p->dosql("insert into userinfo(usr,nickname,readforce,admin,regtime,pwdhash,pwdtipques,pwdtipanshash,memo,realname,email,gbelong,user_style,style) values(?,?,?,0,?,?,?,?,?,?,?,?,?,0)",
        array($_POST['usr'], $_POST['nickname'], $SET['reg_readforce'], time(), encode($_POST['pwd']),
            $_POST['passwordtip'], encode($_POST['passwordtipans']), $_POST['memo'], $_POST['realname'],
            $_POST['email'], $SET['reg_defgroup'], $SET['user_style']));
    $d = array('uid' => DataAccess::connection()->lastInsertId(), 'pwdhash' => encode($_POST['pwd']));
    startLogin($d);
    rememberLogin($d);
    提示("用户 {$_POST['usr']} 注册成功！生成头像缓存需要一定时间等耐心等待……<p>之后你可以更改你的个人信息看看。",取路径("user/panel.php"));
} else
异常("用户 {$_POST['usr']} 已存在！",取路径("user/register.php?accept=1"));
?>
