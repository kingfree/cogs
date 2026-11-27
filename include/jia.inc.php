<?php
/**
 * 旧题目模板可以在设置 $Jia 后引入这个分享按钮。例：$Jia = ['url'=>'problem/problem.php?pid=1','summary'=>'练习','title'=>'求和'];
 * 当前默认页面没有载入这个片段；保留旧站的分享根地址供原模板使用。
 */
?>
<div class="jiathis_style pull-right"><span class="jiathis_txt"></span>
<a href="https://www.jiathis.com/share?uid=1692865" class="jiathis jiathis_txt jiathis_separator jtico jtico_jiathis" target="_blank" rel="noopener"></a>
<a class="jiathis_button_tsina"></a>
<a class="jiathis_button_tqq"></a>
<a class="jiathis_button_qzone"></a>
<a class="jiathis_button_renren"></a>
<a class="jiathis_counter_style"></a>
</div>
<script type="text/javascript">
var jiathis_config = {
    data_track_clickback: true,                         // 保留原分享服务的回访统计。
    siteNum: 5,
    sm: 'tsina,tqq,qzone,renren,tieba',                 // 更多面板沿用旧模板的五个目的地。
    url: <?=json_encode('http://cojs.tk/cogs/'.$Jia['url'], JSON_HEX_TAG)?>,
    summary: <?=json_encode($Jia['summary'], JSON_HEX_TAG)?>,  // 摘要按 JS 字符串编码，保留换行和引号。
    title: <?=json_encode('#COGS# '.$Jia['title'], JSON_HEX_TAG)?>,
    boldNum: 1,
    hideMore: false                                    // 显示“更多分享”按钮。
};
</script>
<script type="text/javascript" src="https://v3.jiathis.com/code_mini/jia.js?uid=1692865" charset="utf-8"></script>
