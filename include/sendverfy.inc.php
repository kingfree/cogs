<?php
$p=new DataAccess();
$sql="select * from userinfo where uid='{$uid}'";
$p->dosql($sql);
$d=$p->rtnrlt(0);
$email=$d['email'];
$code=$d['pwdhash'];
$nick=$d['nickname'];
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";
$headers .= "To: {$email} <{$email}>\r\n";
$headers .= "From: {$SETTINGS['global_sitename']} <register@{$_SERVER['HTTP_HOST']}>\r\n";

$link="{$_SERVER['HTTP_REFERER']}?code={$code}&uid={$_SESSION['ID']}&action=verfy";
$subject="您在 {$SETTINGS['global_sitename']} 注册用户的验证邮件";
$message="<html>
<body>
<p>{$nick}</p>
<p>这封信是由 {$SETTINGS['global_sitename']} 发送的。</p>
<p>您收到这封邮件，是因为在我们系统的新用户注册，或用户修改 Email 使用了您的地址。</p>
<p>如果您并没有访问过我们的系统，或没有进行上述操作，请忽略这封邮件。您不需要退订或进行其他进一步的操作。</p>
<p> 您是我们论坛的新用户，或在修改您的注册 Email 时使用了本地址，我们需 要对您的地址有效性进行验证以避免垃圾邮件或地址被滥用。    您只需点击下面的链接即可激活您的帐号</p>
<p><a href='{$link}'>{$link}</a></p>
<p>(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)</p>
<p>感谢您的访问，祝您使用愉快！ </p>
<p>此致</p>
</body>
</html>";
mail($email,$subject,$message,$headers);

?>

