<?php
require_once("../include/header.php");
gethead(8,"sess","");
$uid = (int)$_SESSION['ID'];

$_POST['usr']=htmlspecialchars($_POST['usr']);
$_POST['nickname']=htmlspecialchars($_POST['nickname']);
$_POST['realname']=htmlspecialchars($_POST['realname']);
$_POST['email']=htmlspecialchars($_POST['email']);
$_POST['user_style']=htmlspecialchars($_POST['user_style']);
$_POST['memo']=htmlspecialchars($_POST['memo']);

$regpage=取路径("user/panel.php");

if ($_POST['action']=="edit") {
if(!preg_match('/([_a-z0-9]{1,24})/', $_POST['usr']))
异常("用户名长度必须在[1,24]中且只能使用英文字母、数字以及_。", $regpage);
    if($_FILES['file']['tmp_name']) {
        $portrait=$SET['base']."images/background";
        $path = pathconvert($SET['cur'],$portrait).'/';
        $backfile = $path . $uid . ".png";
        $image = $_FILES['file']['tmp_name'];
        $type = getimagesize($image);
        if (!$type || !in_array($type[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF], true)) 异常("图片格式无效");
        $process = proc_open(['convert', $image, 'png:'.$backfile], [], $pipes);
        if (!is_resource($process) || proc_close($process) !== 0) 异常("背景图片保存失败");
    }
    $p=new DataAccess();
    $sql = new SqlQuery("select * from userinfo where usr=? and uid!=?", [$_POST['usr'], $uid]);
    $cnt=$p->dosql($sql);
    if ($cnt==0) {
        $_POST['memo']=trim($_POST['memo']);
        $_POST['user_style']=trim($_POST['user_style']);
if(!preg_match('/(\S{1,20})/', $_POST['nickname']))
异常("昵称长度必须在[1,20]中。", $regpage);
if(!preg_match('/(\S*@\S*\.\S*)/', $_POST['email']))
异常("电子邮箱格式不正确。", $regpage);
if(!preg_match('/(\S{0,8})/', $_POST['realname']))
异常("真实姓名长度必须在[0,8]中，应该是汉字。", $regpage);
        $sql = new SqlQuery("update userinfo set usr=?,nickname=?,realname=?,email=?,memo=?,user_style=?,style=? where uid=?", [$_POST['usr'], $_POST['nickname'], $_POST['realname'], $_POST['email'], $_POST['memo'], $_POST['user_style'], $_POST['style'], $uid]);
        $p->dosql($sql);
        $LIB->get_userinfo($uid);
        提示("用户设置信息修改成功！", 取路径("user/panel.php"));
    } else
        异常("用户 {$_POST['usr']} 已存在！",取路径("user/panel.php"));
} else if ($_POST['action']=="editpwd") {
if(!preg_match('/(.{0,24})/', $_POST['npwd1']))
异常("密码长度必须在[0,24]中。", $regpage);
    if ($_POST['npwd1']==$_POST['npwd2']) {
        $p=new DataAccess();
        $sql = new SqlQuery("select pwdhash,nickname from userinfo where uid=?", [$uid]);
        $p->dosql($sql);
        $d=$p->rtnrlt(0);
        if (hash_equals($d['pwdhash'], encode($_POST['opwd']))) {
            $sql = new SqlQuery("update userinfo set pwdhash=? where uid=?", [encode($_POST['npwd1']), $uid]);
            $p->dosql($sql);
            提示("用户 $nickname 的密码修改成功！", 取路径("user/panel.php"));
        } else
            异常("旧密码不正确！", 取路径("user/panel.php"));
    } else {
        异常("两次输入的密码不匹配！", 取路径("user/panel.php"));
    }
} else if ($_POST['action']=="editpwdans") {
    $p=new DataAccess();
    $sql = new SqlQuery("select pwdhash,nickname from userinfo where uid=?", [$uid]);
    $p->dosql($sql);
    $d=$p->rtnrlt(0);
    if (hash_equals($d['pwdhash'], encode($_POST['opwd']))) {
if(!preg_match('/(.{0,64})/', $_POST['passwordtip']))
异常("提示问题长度必须在[0,64]中。", $regpage);
if(!preg_match('/(.{0,64})/', $_POST['passwordtipans']))
异常("提示问题答案长度必须在[0,64]中。", $regpage);
        $que=htmlspecialchars($_POST['qus']);
        $ans=encode($_POST['ans']);
        $sql = new SqlQuery("update userinfo set pwdtipques=?,pwdtipanshash=? where uid=?", [$que, $ans, $uid]);
        $p->dosql($sql);
        提示("用户 $nickname 的密码提示问题修改成功！", 取路径("user/panel.php"));
    } else
        异常("旧密码不正确！", 取路径("user/panel.php"));
}

?>
