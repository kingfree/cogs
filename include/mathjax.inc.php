<?php
/**
 * 题面或文章在页头之后引入公式配置。例：require_once __DIR__.'/../include/mathjax.inc.php';
 * 先声明站内题面支持的 TeX 标记，再加载部署时提供的 MathJax 主程序。
 */
?>
<script type="text/x-mathjax-config">
MathJax.HTML.Cookie.Set("menu", {});                      // 每次打开题页使用本站的默认公式设置。
MathJax.Hub.Config({
    extensions: ["tex2jax.js"],                           // 从题面文字识别 TeX 片段。
    jax: ["input/TeX", "output/HTML-CSS"],                // 将 TeX 排成能随正文换行的 HTML。
    "HTML-CSS": {
        availableFonts: [],
        styles: {".MathJax_Preview": {visibility: "hidden"}}       // 排版结束前隐藏预览占位符。
    },
    tex2jax: {
        inlineMath: [['$', '$'], ['\\(', '\\)']],           // 兼容旧题面两种行内公式分隔符。
        processEscapes: true                              // 反斜杠转义的美元符号仍作普通文字。
    },
    TeX: {extensions: ["mhchem.js"]}                       // 化学题使用 TeX 的化学式扩展。
});
(function (hub) {
    var minimum = {Firefox: 3, Opera: 9.52, MSIE: 6, Chrome: 0.3, Safari: 2, Konqueror: 4, Unknown: 10000};
    if (!hub.Browser.versionAtLeast(minimum[hub.Browser] || 0)) {  // 沿用旧站列明的最低浏览器版本。
        hub.Config({jax: [], extensions: [], "v1.0-compatible": false});  // 不兼容时保留可阅读的公式原文。
    }
})(MathJax.Hub);
</script>
<script type="text/javascript" src="/MathJax/MathJax.js"></script>
