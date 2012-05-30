</div>
<!--<p class='alert alert-success'><?php echo 输出文本($SET['global_tail']) ?></p>-->
<div class='footer'>
<p class='pull-right'><a href="#" onclick="document.getElementById('trad').innerHTML='中文正體';document.getElementById('alltext').innerHTML = TradSimp.getTrad(document.getElementById('alltext').innerHTML);" id="trad">中文繁體</a>/<a href="#" onclick="document.getElementById('trad').innerHTML='中文繁體';document.getElementById('alltext').innerHTML = TradSimp.getSimp(document.getElementById('alltext').innerHTML);" id="simp">中文简体</a></p>
<p>
应用 <a href="http://twitter.github.com/bootstrap/">Twitter Bootstrap</a> 作为界面框架。
<?php echo 输出文本("应用样式 %style_profile% 。进程运行 %processtime% s ，处理完成数据库 %querytimes% 次。") ?>
</p>
<p>由 CmYkRgB123 在线评测系统强力驱动，版本 <?php echo $cfg['Version']; ?> ，由<a href=<?=路径("user/detail.php?uid=524")?> target="_blank">王者自由</a>维护。版权所有 &copy; <a href="http://www.byvoid.com" target="_blank">BYVoid</a>，保留部分权利。</p>
</div>
</div>
<script type="text/javascript" src="<?=路径("include/jquery.js")?>"></script>
<script type="text/javascript" src="<?=路径("include/sortTable.js")?>"></script>
<script type="text/javascript" src="<?=路径("style/bootstrap/js/bootstrap.js")?>"></script>
<script type="text/javascript">
$("a[rel=popover]").popover()
</script>
<script type="text/javascript">
jQuery(function($)) {
$('div.btn-group[data-toggle-name=*]').each(function(){
  var group   = $(this);
  var form    = group.parents('form').eq(0);
  var name    = group.attr('data-toggle-name');
  var hidden  = $('input[name="' + name + '"]', form);
  $('button', group).each(function(){
    var button = $(this);
    button.live('click', function(){
      hidden.val($(this).val());
    });
    if(button.val() == hidden.val()) {
      button.addClass('active');
    }
  });
});
});
</script>

</body>
</html>
