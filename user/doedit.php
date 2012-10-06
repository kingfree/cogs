<?php
require_once("../include/header.php");
gethead(8,"sess","");
过滤();
$_POST['usr']=htmlspecialchars($_POST['usr']);
$_POST['nickname']=htmlspecialchars($_POST['nickname']);
$_POST['realname']=htmlspecialchars($_POST['realname']);
$_POST['email']=htmlspecialchars($_POST['email']);
$_POST['user_style']=htmlspecialchars($_POST['user_style']);
$_POST['memo']=htmlspecialchars($_POST['memo']);

$regpage=取路径("user/panel.php");

if ($_POST[action]=="edit") {
if(!preg_match('/([a-z0-9][_a-z0-9]{3,23})/', $_POST['usr']))
异常("用户名长度必须在[4,24]中且只能使用英文字母、数字以及_，并且首字符必须为字母或数字。", $regpage);
    if($_FILES['file']['tmp_name']) {
        $portrait=$SET['base']."images/background";
        $path = pathconvert($SET['cur'],$portrait).'/';
        $backfile = $path . $_POST['uid'] . ".png";
        if(file_exists($backfile)) {
            $cmd = "rm $backfile";
            $hr = popen($cmd, 'r');
            pclose($hr);
        }
        $cmd = "convert {$_FILES['file']['tmp_name']} $backfile";
        $hr = popen($cmd, 'r');
        pclose($hr);
    }
    $p=new DataAccess();
    $sql="select * from userinfo where usr='{$_POST['usr']}' and uid!={$_POST['uid']}";
    $cnt=$p->dosql($sql);
    if ($cnt==0) {
        $_POST['memo']=trim($_POST['memo']);
        $_POST['user_style']=trim($_POST['user_style']);
if(!preg_match('/(\S{2,20})/', $_POST['nickname']))
异常("昵称长度必须在[2,20]中。", $regpage);
if(!preg_match('/(\S*@\S*\.\S*)/', $_POST['email']))
异常("电子邮箱格式不正确。", $regpage);
if(!preg_match('/(\S{2,8})/', $_POST['realname']))
异常("真实姓名长度必须在[2,8]中，应该是汉字。", $regpage);
        $sql="update userinfo set usr='{$_POST['usr']}',nickname='{$_POST['nickname']}',realname='{$_POST['realname']}',email='{$_POST['email']}',memo='{$_POST['memo']}',user_style='{$_POST['user_style']}',style='{$_POST['style']}' where uid={$_POST['uid']}";
        $p->dosql($sql);
        $LIB->get_userinfo($_GET['uid']);
        提示("用户设置信息修改成功！", 取路径("user/panel.php"));
    } else
        异常("用户 {$_POST['usr']} 已存在！",取路径("user/panel.php"));
} else if ($_POST[action]=="editpwd") {
if(!preg_match('/(.{4,24})/', $_POST['npwd1']))
异常("密码长度必须在[4,24]中。", $regpage);
    if ($_POST[npwd1]==$_POST[npwd2]) {
        $p=new DataAccess();
        $sql="select pwdhash,nickname from userinfo where uid={$_GET[uid]}";
        $p->dosql($sql);
        $d=$p->rtnrlt(0);
        if ($d[pwdhash]==encode($_POST[opwd])) {
            $sql="update userinfo set pwdhash='". encode($_POST[npwd1]) ."' where uid={$_GET[uid]}";
            $p->dosql($sql);
            提示("用户 $nickname 的密码修改成功！", 取路径("user/panel.php"));
        } else
            异常("旧密码不正确！", 取路径("user/panel.php"));
    } else {
        异常("两次输入的密码不匹配！", 取路径("user/panel.php"));
    }
} else if ($_POST[action]=="editpwdans") {
    $p=new DataAccess();
    $sql="select pwdhash,nickname from userinfo where uid={$_GET[uid]}";
    $p->dosql($sql);
    $d=$p->rtnrlt(0);
    $uid=(int)$_GET['uid'];
    if ($d['pwdhash']==encode($_POST['opwd'])) {
if(!preg_match('/(.{2,64})/', $_POST['passwordtip']))
异常("提示问题长度必须在[2,64]中。", $regpage);
if(!preg_match('/(.{2,64})/', $_POST['passwordtipans']))
异常("提示问题答案长度必须在[2,64]中。", $regpage);
        $que=htmlspecialchars($_POST['qus']);
        $ans=encode($_POST['ans']);
        $sql="update userinfo set pwdtipques='{$que}',pwdtipanshash='{$ans}' where uid={$uid}";
        $p->dosql($sql);
        提示("用户 $nickname 的密码提示问题修改成功！", 取路径("user/panel.php"));
    } else
        异常("旧密码不正确！", 取路径("user/panel.php"));
}

?>
