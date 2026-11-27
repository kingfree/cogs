<?php
// 站点地图。打开 docs/map.php 列出题目、文章等链接，题目和文章按当前访问者的阅读范围筛选。
require_once("../include/header.php");
gethead(1,"","站点地图");
?>
<div class='row-fluid'>
<div class='span12'>
<div class='page'>

<h1>COGS 站点地图</h1>

<h2><a href="../problem/catelist.php">分类</a></h2>
<?php
$pRows = Category::directory();
$cnt = count($pRows);
foreach ($pRows as $i => $d) {
    echo "<a href=\"../problem/index.php?caid={$d['caid']}\">".h($d['cname'])."</a>\n";
}
?>
<h2><a href="../page/index.php">页面</a></h2>
<?php
$pRows = Page::directory();
$cnt = count($pRows);
foreach ($pRows as $i => $d) {
    echo "<a href=\"../page/page.php?aid={$d['aid']}\">".h($d['title'])."</a>\n";
}
?>
<h2><a href="../problem/index.php">题目</a></h2>
<?php
$pRows = Problem::directory();
$cnt = count($pRows);
foreach ($pRows as $i => $d) {
    echo "<a href=\"../problem/problem.php?pid={$d['pid']}\">".h($d['probname'])." (".h($d['filename']).")</a>\n";
}
?>
<h2><a href="../contest/index.php">比赛</a></h2>
<?php
$pRows = ContestRound::directory();
$cnt = count($pRows);
foreach ($pRows as $i => $d) {
    echo "<span id='{$d['ctid']}'>";
    echo "<a href=\"../contest/problem.php?ctid={$d['ctid']}\">".h($d['cname'])."</a>\n";
    echo nl2br(sp2n(htmlspecialchars($d['intro'])));
    echo "</span>";
}
?>
<h2><a href="../user/index.php">用户</a></h2>
<?php
$pRows = Account::rankDirectory();

foreach ($pRows as $i => $d) {
    echo "<a href=\"../user/detail.php?uid={$d['uid']}\">".h($d['nickname'])." (".h($d['usr']).")</a>\n";
}
?>
<h2><a href="../docs/index.php">文档</a></h2>
<a href="../user/login.php">登录</a>
<a href="../user/register.php">注册</a>
<h2><a href="../docs/map.php">地图</a></h2>
<?php echo 输出文本($SET['global_map']) ?>
</div>
</div>
<?php
    include_once("../include/footer.php");
?>