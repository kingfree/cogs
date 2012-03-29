<?php
session_start();
if (!(time()-$_SESSION['install']<=600)) exit;
if ($_POST[comefrom]=="initenv") 
{
	$fileenv="env.inc.php";
	chdir("../include");
	ob_start();
	var_export($_POST[cfg]);
	$str=ob_get_clean();
	$fp=fopen($fileenv,"w");
	fwrite($fp,'<?php $cfg='.$str.' ?>');
	fclose($fp);
	ob_end_flush();
}
else
{
	echo "非法";
	exit;
}

include_once("../include/stdlib.php");
$LIB->cls_dbctrl(); 
$dbc=new DBControl;
$dbc->deletetable();
$dbc->createtable();
$_SESSION['installsucc']=1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>数据库设置</title>
</head>

<body>
<p>数据表创建成功！</p>
<p><a href=".">安装完成</a></p>
</body>
</html>
