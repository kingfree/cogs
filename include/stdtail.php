</div>
<div id="publictail">
<a href="#" onclick="document.getElementById('trad').innerHTML='正';document.getElementById('alltext').innerHTML = TradSimp.getTrad(document.getElementById('alltext').innerHTML);" id="trad">繁</a>/<a href="#" onclick="document.getElementById('trad').innerHTML='繁';document.getElementById('alltext').innerHTML = TradSimp.getSimp(document.getElementById('alltext').innerHTML);" id="simp">简</a>
<?php echo 输出文本($SET['global_tail']) ?>
</div>
<div id="global_tail">
<?php echo 输出文本("应用样式 %style_profile% 。进程运行 %processtime% s ，处理完成数据库 %querytimes% 次。") ?>
<br />
由 CmYkRgB123 在线评测系统强力驱动，版本 <?php echo $cfg['Version']; ?> ，由<a href=<?=路径("user/detail.php?uid=524")?> target="_blank">王者自由</a>维护。版权所有 &copy; <a href="http://www.byvoid.com" target="_blank">BYVoid</a>，保留部分权利。
</div>
</div>
</body>
</html>
