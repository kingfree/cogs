<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Tylie" content="text/html; charset=utf-8" />
<title>状态查询</title>
</head>
<body>
<h3>评测机状态</h3>
<ul>
<li>评测机：<?echo $cfg['Name'] ?></li>
<li>版本：<?echo $cfg['Ver'] ?></li>
<li>当前状态：<?echo read();?></li>
<li>评测次数：<?echo read_cnt();?></li>
<li>正在运行：<?echo getrunning();?></li>
</ul>
<h3>编译选项</h3>
<pre>fpc {$query['src']} -So -XS -v0 -O1 -o\"{$query['pname']}\"</pre>
<pre>gcc {$query['src']} -lm -w -O2 -static -o {$query['pname']}</pre>
<pre>g++ {$query['src']} -lm -w -O2 -static -o {$query['pname']}</pre>
<h3>编译器版本</h3>
<pre><?passthru("fpc -v")?></pre>
<pre><?passthru("gcc --version")?></pre>
<pre><?passthru("g++ --version")?></pre>
</body>
</html>
