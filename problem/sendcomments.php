<?php
require_once("../include/header.php");
gethead(8,"sess","");
$p=new DataAccess();
$scd=(int)$_POST['showcode'];
$uid=(int)$_SESSION['ID'];
$pid=(int)$_POST['pid'];
$aid=(int)$_POST['aid'];
$cid=(int)$_POST['cid'];
$detail=mysql_real_escape_string($_POST['detail']);
$showcode=(int)$_POST['showcode'];
$tm=time();

if($cid) {
    $sql="update comments set detail='{$detail}', stime={$tm} ,showcode={$showcode} where cid={$cid}";
} else if($pid) {
    $sql="insert into comments(pid,uid,detail,stime,showcode) values({$pid},{$uid},'{$detail}',{$tm} ,{$showcode})";
} else if($aid) {
    $sql="insert into comments(aid,uid,detail,stime,showcode) values({$aid},{$uid},'{$detail}',{$tm} ,{$showcode})";
} else {
    异常("发表评论失败！",取路径("problem/comments.php"));
}
$cnt=$p->dosql($sql);
if($pid) {
提示("发表评论成功！",取路径("problem/comments.php?pid={$pid}"));
} else if($aid) {
提示("发表评论成功！",取路径("problem/comments.php?aid={$aid}"));
}
?>
