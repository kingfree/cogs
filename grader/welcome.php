<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Tylie" content="text/html; charset=utf-8" />
<title>状态查询</title>
</head>
<body>
<ul>
<li>评测机：<?echo $cfg['Name'] ?></li>
<li>版本：<?echo $cfg['Ver'] ?></li>
<li>当前状态：<?echo read();?></li>
<li>评测次数：<?echo read_cnt();?></li>
<li>Running：<?echo getrunning();?></li>
</ul>
<pre><?passthru("fpc -v")?></pre>
<pre><?passthru("gcc --version")?></pre>
<pre><?passthru("g++ --version")?></pre>
</body>
</html>
