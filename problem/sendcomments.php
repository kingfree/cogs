<?php
require_once("../include/header.php");
gethead(8,"sess","");
$scd=(int)$_POST['showcode'];
$p=new DataAccess();
$uid=(int)$_SESSION['ID'];
$pid=(int)$_POST['pid'];
$cid=(int)$_POST['cid'];
$showcode=(int)$_POST['showcode'];
$tm=time();

if($cid) {
    $sql="update comments set detail='{$detail}', stime={$tm} ,showcode={$showcode} where cid={$cid}";
} else if($pid) {
    $sql="insert into comments(pid,uid,detail,stime,showcode) values({$pid},{$uid},'{$detail}',{$tm} ,{$showcode})";
} else {
    异常("发表评论失败！",取路径("problem/comments.php?pid={$_POST['pid']}"));
}
$cnt=$p->dosql($sql);
提示("发表评论成功！",取路径("problem/comments.php?pid={$_POST['pid']}"));
?>
