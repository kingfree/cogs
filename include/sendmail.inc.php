<?php
function sendmail($email,$subject,$message)
{
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=utf-8\r\n";
	$headers .= "To: {$email} <{$email}>\r\n";
	$headers .= "From: {$SET['global_sitename']} <cogs@{$_SERVER['HTTP_HOST']}>\r\n";
	return mail($email,$subject,$message,$headers);
}
?>

