<?php
session_start();
if (!(time()-$_SESSION['install']<=600)) exit;
	include_once("../include/default.env.inc.php");
	include_once("../include/string.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>环境变量初始化</title>
</head>

<body>
<form id="frm" name="frm" method="post" action="initdb.php">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td>变量</td>
    <td>值</td>
    <td>说明</td>
  </tr>
  <?php 
	foreach($cfg as $k=>$v)
	{
  ?>
  <tr>
    <td><?php echo $k; ?></td>
    <td><input name=cfg[<?php echo $k; ?>] type="text"  class="InputBox" value="<?php echo $v; ?>" size="50"/></td>
    <td><?php echo getdescirbe($k); ?></td>
  </tr>
  <?php 
  	}
  ?>
</table>
<p>
  <input type="submit" name="Submit" value="提交" class="Button" />
  <input name="comefrom" type="hidden" value="initenv" />
</p>
</form>
</body>
</html>
