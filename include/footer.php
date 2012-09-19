</div>
<div class='footer'>
<div class='btn-group buttons-radio pull-right'>
<a class='btn btn-small' onclick="document.getElementById('trad').innerHTML='中文正體';document.getElementById('alltext').innerHTML = TradSimp.getTrad(document.getElementById('alltext').innerHTML);" id="trad">中文繁體</a>
<a class='btn btn-small' onclick="document.getElementById('trad').innerHTML='中文繁體';document.getElementById('alltext').innerHTML = TradSimp.getSimp(document.getElementById('alltext').innerHTML);" id="simp">中文简体</a>
</div>
<p>
应用 <a href="http://twitter.github.com/bootstrap/" target="_blank">Twitter Bootstrap</a> 作为界面框架，应用了 <a href="/Bootstrap/css/<?=$_SESSION['user_style']?>.min.css" target="_blank"><?=$_SESSION['user_style']?>.min.css</a> 主题。
<?php echo 输出文本("进程运行 %processtime% s ，处理完成数据库 %querytimes% 次。") ?>
</p>
<p>
由 <a href="https://github.com/KingFree/COGS-by-Kingfree" target="_blank">CmYkRgB123 在线评测系统</a> 强力驱动，版本 <?php echo $cfg['Version']; ?> 。版权所有 &copy; <a href="http://www.byvoid.com" target="_blank">BYVoid</a>，保留部分权利。
</p>
</div>
</div>
</body>
</html>
