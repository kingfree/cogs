<?php
/**
 * 代码页载入两种已有标记对应的高亮器。例：require_once __DIR__.'/../include/hlighter.inc.php';
 * prettyprint 负责旧题面与带行号的源码，pre code 用于讨论中的 BBCode 代码块。
 */
?>
<script type="text/javascript" src="/kindeditor/plugins/code/prettify.js"></script>
<link rel="stylesheet" type="text/css" href="/kindeditor/plugins/code/prettify.css" />
<style type="text/css">
li.L0, li.L1, li.L2, li.L3, li.L4, li.L5, li.L6, li.L7, li.L8, li.L9 {
    list-style-type: decimal-leading-zero;  /* 每行都保留行号，便于引用编译错误的位置。 */
    margin-left: 2ex;
}
</style>
<script type="text/javascript" src="/highlight.js/highlight.min.js"></script>
<link rel="stylesheet" type="text/css" href="/highlight.js/default.min.css" />
<script type="text/javascript">
jQuery(function($) {
    if (!window.hljs) $('pre code').parent().addClass('prettyprint');  // 仅安装仓库随附插件时，BBCode 也使用 Prettify。
    if (window.prettyPrint) prettyPrint();                            // 处理旧题面及已标记的源码区。
    if (!window.hljs) return;                                        // 可选高亮库缺席时，保留当前渲染结果。
    $('pre code').each(function(index, block) {
        hljs.highlightBlock(block, '    ', true);  // 讨论的代码块沿用四空格的制表符显示。
    });
});
</script>
