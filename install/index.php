<?php
	include_once("../include/env.inc.php");
	session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>欢迎使用COJS</title>
</head>

<body>
<p>欢迎使用COJS</p>
<?php
if($_SESSION['installsucc']==1)
{
?>
<a href="../">进入首页</a>
<?php
exit;
}
if (isset($_POST[sn]))
{
	if (!isset($cfg[safety_sn]))
	{
		$cfg[safety_sn]="CmYkRgB123";
	}
	if ($_POST[sn]==$cfg[safety_sn])
	{
		$_SESSION['install']=time();
	}
}

if (time()-$_SESSION['install']<=600)
{
?>
<p><a href="check.php">进入安装过程</a></p>
<?php
}
else
{
?>
<form id="form1" name="form1" method="post" action="">
  <p>请输入安装许可  </p>
  <p>
    <input name="sn" type="password" id="sn" />
  </p>
  <p>
    <input type="submit" name="Submit" value="提交" />
</p>
</form>
<?php
}
?>
</body>

</html>
