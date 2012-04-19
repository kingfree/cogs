<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>状态查询</title>
</head>

<body>
<p>评测机：<?php echo $cfg['Name'] ?></p>
<p>版本：<?php echo $cfg['Ver'] ?></p>
<p>当前状态：<?php echo read();?></p>
<p>评测次数：<?php echo read_cnt();?></p>
<p>Running：<?php echo getrunning();?></p>
</body>
</html>
