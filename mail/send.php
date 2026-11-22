<?php
require_once("../include/header.php");
gethead(8,"sess","");
$p=new DataAccess();
$sender = (int)$_SESSION['ID'];
$_POST['toid'] = (int)$_POST['toid'];

if($sender < 1) 异常("发件人错误！",取路径("mail/index.php"));
if(!$_POST['toid']) 异常("收件人错误！",取路径("mail/index.php"));
if(!is_string($_POST['title']) || $_POST['title'] === '') 异常("标题错误！",取路径("mail/index.php"));
if(!is_string($_POST['msg']) || $_POST['msg'] === '') 异常("信件内容错误！",取路径("mail/index.php"));

$p->dosql("insert into mail(fromid, toid, time, readed, title, msg) values(?, ?, ?, 0, ?, ?)",
    array($sender, $_POST['toid'], time(), $_POST['title'], $_POST['msg']));

提示("发送邮件成功！",取路径("mail/index.php"));

?>
