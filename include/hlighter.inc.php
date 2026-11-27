<?php /** 题目、讨论和代码页在页头后引入本片段，为带 prettyprint 类的代码块加载高亮样式和脚本。 */ ?><script type="text/javascript" src="/kindeditor/plugins/code/prettify.js"></script>
<link rel=stylesheet type="text/css" href="/kindeditor/plugins/code/prettify.css" />
<style type='text/css'>
li.L0, li.L1, li.L2, li.L3, li.L4, li.L5, li.L6, li.L7, li.L8, li.L9 {
    list-style-type: decimal-leading-zero;
    margin-left: 2ex;
}
</style>

<script type="text/javascript" src="/highlight.js/highlight.min.js"></script>
<link rel=stylesheet type="text/css" href="/highlight.js/default.min.css" />
<script type="text/javascript">
jQuery(function($) {
  prettyPrint();
  $('pre code').each(function(i, e) {
      hljs.highlightBlock(e, '    ', true)
  });
});
</script>

