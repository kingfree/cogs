<?php
require_once("../include/header.php");
gethead(8,"修改用户","");
$p=new DataAccess();
过滤();

if ($_GET[action]=="del") {
    $sql="select admin,uid from userinfo where uid={$_GET[uid]}";
    $cnt=$p->dosql($sql);
    if(!$cnt) 异常("无此用户！",取路径("user/index.php"));
    $d=$p->rtnrlt(0);
    $sql="delete from userinfo where uid={$_GET[uid]}";
    $p->dosql($sql);
    提示("删除用户成功！",取路径("user/index.php"));
} else if ($_GET[action]=="edit") {
    $tt="";
    $sql="update userinfo set nickname='{$_POST[nickname]}' ,readforce={$_POST[readforce]} ,email='{$_POST[email]}',grade={$_POST[grade]},memo='{$_POST[memo]}',realname='{$_POST[realname]}',gbelong={$_POST[gbelong]} where uid={$_GET[uid]}";
    $p->dosql($sql);
    if ($_POST['reset'] == "reset") {
        $sql="update userinfo set pwdhash='".encode("")."' where uid={$_GET[uid]}";
        $p->dosql($sql);
        $ttt="并且重置密码为空。";
    }
    提示("编辑用户成功！$ttt",取路径("user/detail.php?uid={$_GET['uid']}"));
}
?>
