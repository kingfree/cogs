</div>
<!--<p class='alert alert-success'><?php echo 输出文本($SET['global_tail']) ?></p>-->
<div class='footer'>
<p class='pull-right'><a href="#" onclick="document.getElementById('trad').innerHTML='中文正體';document.getElementById('alltext').innerHTML = TradSimp.getTrad(document.getElementById('alltext').innerHTML);" id="trad">中文繁體</a>/<a href="#" onclick="document.getElementById('trad').innerHTML='中文繁體';document.getElementById('alltext').innerHTML = TradSimp.getSimp(document.getElementById('alltext').innerHTML);" id="simp">中文简体</a></p>
<p><?php echo 输出文本("应用样式 %style_profile% 。进程运行 %processtime% s ，处理完成数据库 %querytimes% 次。") ?></p>
<p>由 CmYkRgB123 在线评测系统强力驱动，版本 <?php echo $cfg['Version']; ?> ，由<a href=<?=路径("user/detail.php?uid=524")?> target="_blank">王者自由</a>维护。版权所有 &copy; <a href="http://www.byvoid.com" target="_blank">BYVoid</a>，保留部分权利。</p>
</div>
</div>
<script type="text/javascript" src="<?=路径("style/bootstrap/js/bootstrap.min.js")?>"></script>
</body>
</html>
