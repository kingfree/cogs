<?php
// 处理 mail/index.php 写信表单中的 toid、title 和 msg，以当前登录账号为发件人。
require_once("../include/header.php");
gethead(8,"sess","");
requirePost();
// 收件人由表单选择，发件人始终使用服务器保存的登录身份。
$sender = (int)$_SESSION['ID'];
$_POST['toid'] = (int)$_POST['toid'];

if($sender < 1) 异常("发件人错误！",取路径("mail/index.php"));
if(!$_POST['toid']) 异常("收件人错误！",取路径("mail/index.php"));
if(!is_string($_POST['title']) || $_POST['title'] === '') 异常("标题错误！",取路径("mail/index.php"));
if(!is_string($_POST['msg']) || $_POST['msg'] === '') 异常("信件内容错误！",取路径("mail/index.php"));

Message::send($sender, $_POST['toid'], time(), $_POST['title'], $_POST['msg']);

提示("发送邮件成功！",取路径("mail/index.php"));

?>
