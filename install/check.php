<?php
session_start();
if (!(time()-$_SESSION['install']<=600)) exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>检查系统</title>
</head>
<body>
<?php
	$sta[curr_os]=PHP_OS;
	$sta[phpv]=PHP_VERSION;
	
	if($sta[phpv] < '5.0.0')
	{
		$fail=1;
		$msg="PHP版本过低";
	}
	
	$sta[serv]=$_SERVER['SERVER_SOFTWARE'];
	$sta[root]=$_SERVER['DOCUMENT_ROOT'];
	$sta[ipport]=$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'];
	
	if (function_exists('mysql_connect'))
		$sta[mysql]=mysql_get_client_info();
	else
	{
		$sta[mysql]="无";
		$msg="找不到MySql";
		$fail=1;
	}
	
	$sta[max_size] = @ini_get(upload_max_filesize);
	$sta[disk_space] = intval(diskfreespace('.') / (1024 * 1024)).'M';
?>
<table width="100%" border="0">
  <tr>
    <th scope="row">操作系统</th>
    <td><?php echo $sta[curr_os] ?></td>
  </tr>
  <tr>
    <th scope="row">服务器软件</th>
    <td><?php echo $sta[serv] ?></td>
  </tr>
  <tr>
    <th scope="row">文档路径</th>
    <td><?php echo $sta[root] ?></td>
  </tr>
  <tr>
    <th scope="row">服务器IP:端口</th>
    <td><?php echo $sta[ipport] ?></td>
  </tr>
  <tr>
    <th scope="row">PHP</th>
    <td><?php echo $sta[phpv] ?></td>
  </tr>
  <tr>
    <th scope="row">MySql</th>
    <td><?php echo $sta[mysql] ?></td>
  </tr>
  <tr>
    <th scope="row">上传限制</th>
    <td><?php echo $sta[max_size] ?></td>
  </tr>
  <tr>
    <th scope="row">磁盘空间</th>
    <td><?php echo $sta[disk_space] ?></td>
  </tr>
</table>
<p>
  <?php
if ($fail)
{
	echo $msg;
}
else
{
?>
你的服务器可以正常配置</p>
<p><a href="initenv.php">进入下一步</a></p>
<?php
}
?>
</body>
</html>
