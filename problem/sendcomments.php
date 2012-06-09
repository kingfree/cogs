<?php
require_once("../include/header.php");
gethead(8,"sess","");
$scd=(int)$_POST['showcode'];
$p=new DataAccess();
$sql="select * from comments where pid={$_POST['pid']} and uid={$_SESSION[ID]}";
$cnt=$p->dosql($sql);
$tm=time();
if (!$cnt) {
    $sql="insert into comments(pid,uid,detail,stime,showcode) values({$_POST['pid']},{$_SESSION[ID]},'{$_POST['detail']}',{$tm} ,{$scd})";
} else {
    $sql="update comments set detail='{$_POST['detail']}', stime={$tm} ,showcode={$scd} where pid={$_POST['pid']} and uid={$_SESSION[ID]}";
}
$p->dosql($sql);

提示("发表评论成功！",取路径("problem/comments.php?pid={$_POST['pid']}"));

?>