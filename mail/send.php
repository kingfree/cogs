<?php
require_once("../include/stdhead.php");
gethead(8,"sess","");
$p=new DataAccess();
过滤();
$_POST['fromid'] = (int)$_POST['fromid'];
$_POST['toid'] = (int)$_POST['toid'];

if(!$_POST['fromid']) 异常("发件人错误！",取路径("mail/index.php"));
if(!$_POST['toid']) 异常("收件人错误！",取路径("mail/index.php"));
if(!$_POST['title']) 异常("标题错误！",取路径("mail/index.php"));
if(!$_POST['msg']) 异常("信件内容错误！",取路径("mail/index.php"));

$sql = "insert into mail(mid, fromid, toid, time, readed, title, msg) values(0, {$_POST['fromid']}, {$_POST['toid']}, ".time().", 0, '{$_POST['title']}', '{$_POST['msg']}')";
$p->dosql($sql);// or die(mysql_error());

提示("发送邮件成功！",取路径("mail/index.php"));

?>
