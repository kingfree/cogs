<?php
/**
 * 页头打开的页面用 require __DIR__.'/../include/footer.php'; 输出统计、主题和站点页脚，并关闭页面容器。
 * 快捷键只操作当前页面上存在的控件；编辑表单的文字时保留正常输入。
 */
?>
</div>
<div class='footer'>
<div class='btn-group buttons-radio pull-right'>
<a class='btn btn-small' onclick="TradSimp.display(document.getElementById('alltext'), true); this.firstChild.nodeValue='中文正體';" id="trad">中文繁體</a>
<a class='btn btn-small' onclick="TradSimp.display(document.getElementById('alltext'), false); document.getElementById('trad').firstChild.nodeValue='中文繁體';" id="simp">中文简体</a>
</div>
<p>
<span class='tou'><?=date('Y-m-d H:i:s') /* 使用站点时区显示当前时间。 */ ?></span>
以 <a href="http://getbootstrap.com/" target="_blank">Twitter Bootstrap</a> 作为界面框架，应用 <a href="/Bootstrap/css/<?=rawurlencode($_SESSION['user_style'])?>.min.css" target="_blank"><?=h($_SESSION['user_style'])?></a> 主题。
<?=输出文本("进程运行 %processtime% s ，处理完成数据库 %querytimes% 次。") /* 此时业务查询已完成。 */ ?>
</p>
<?=输出文本($SET['global_tail']) /* 此处展示管理员配置的页尾模板。 */ ?>
<p>由 <a href="https://github.com/kingfree/cogs" target="_blank">CmYkRgB123 在线评测系统</a> 强力驱动，版本 <?=h($cfg['Version'])?> ，由 <a href="http://blog.kingfree.moe" target="_blank">王者自由</a> 二次开发。原作者 &copy; <a href="http://www.byvoid.com" target="_blank">BYVoid</a>，保留部分权利。</p>
</div>
</div>
<script>
$(document).ready(function() {
    $('#fenleito').click(fenlei);                      // 题库按钮展开或收起分类清单。
    $('#chbar').click(chbar);                          // 题目详情页可以手动切换侧栏。
});
function fenlei() {
    $('#fenlei').slideToggle();                        // 按按钮或 T 键都切换同一份分类清单。
}
function chbar() {
    var hide = $('#rightbar').hasClass('span8');       // 右栏占八格表明左栏目前可见。
    $('#leftbar').toggle(!hide);                       // 同步切换左栏和正文宽度。
    $('#rightbar').toggleClass('span8', !hide);
    $('#chbaricon').toggleClass('icon-indent-left', !hide).toggleClass('icon-align-left', hide);  // 图标预告下一次动作。
    $('#chbar').attr('title', hide ? '显示左边栏' : '隐藏左边栏');
}
function pagedown() {
    $('body').animate({scrollTop:$('body').scrollTop()+400});  // J 键向下读约一段题面。
}
function pageup() {
    $('body').animate({scrollTop:$('body').scrollTop()-400});  // K 键按同样距离向上翻。
}
$(document).keydown(function(key) {
    if (key.ctrlKey && key.which === 13) {             // 在提交代码的输入区也允许 Ctrl+Enter 送出。
        $('#tijiao').submit();
        return;
    }
    if (key.which === 27) { $('.modal').modal('hide'); return; }  // 对话框内的输入字段也接受 Esc。
    if ($(key.target).is('input,textarea,select') || key.target.isContentEditable) return;  // 输入题解或口令时，字母仍属于正在编辑的字段。
    if (key.ctrlKey || key.altKey || key.metaKey) return;  // 保留浏览器复制、刷新等系统组合键。
    switch (key.which) {
        case 72: chbar(); break;                        // H 切换侧栏。
        case 74: pagedown(); break;                     // J 向下阅读。
        case 75: pageup(); break;                       // K 向上阅读。
        case 84: fenlei(); break;                       // T 展开分类。
        case 82: $('#addfenlei').click(); break;        // R 打开添加分类的表单。
        case 65: $('#source').click(); break;           // A 选择要上传的源码。
        case 13: $('#addcate form').submit(); break;    // Enter 提交已打开的分类表单。
        case 80: $('#pas').prop('checked', true); break;  // P 选择 Pascal。
        case 67: $('#c').prop('checked', true); break;    // C 选择 C。
        case 187: $('#cpp').prop('checked', true); break; // 加号选择 C++。
        case 85: $('#testmode').prop('checked', !$('#testmode').prop('checked')); break;  // U 切换本地测试模式。
    }
});
</script>
</body>
</html>
