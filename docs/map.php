<?php
// 站点地图。打开 docs/map.php 列出题目、文章等链接，题目和文章按当前访问者的阅读范围筛选。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","站点地图");  // 地图本身对游客开放，目录查询决定各条目的可读范围。
?>
<div class='row-fluid'>
<div class='span12'>
<div class='page'>

<h1>COGS 站点地图</h1>

<h2><a href="../problem/catelist.php">分类</a></h2>
<?php
foreach (Category::directory() as $category) {  // 列出分类名称，链接按固定分类编号筛选题库。
    echo "<a href=\"../problem/index.php?caid={$category['caid']}\">".h($category['cname'])."</a>\n";
}
?>
<h2><a href="../page/index.php">页面</a></h2>
<?php
foreach (Page::directory() as $article) {  // 读取访问者可见的文章，并链接到正文。
    echo "<a href=\"../page/page.php?aid={$article['aid']}\">".h($article['title'])."</a>\n";
}
?>
<h2><a href="../problem/index.php">题目</a></h2>
<?php
foreach (Problem::directory() as $problem) {  // 只显示当前可读的题目，名称旁附上提交使用的文件名。
    echo "<a href=\"../problem/problem.php?pid={$problem['pid']}\">".h($problem['probname'])." (".h($problem['filename']).")</a>\n";
}
?>
<h2><a href="../contest/index.php">比赛</a></h2>
<?php
foreach (ContestRound::directory() as $round) {  // 比赛介绍对应场次，链接使用场次编号。
    echo "<span id='{$round['ctid']}'>";
    echo "<a href=\"../contest/problem.php?ctid={$round['ctid']}\">".h($round['cname'])."</a>\n";
    echo nl2br(sp2n(htmlspecialchars($round['intro'])));  // 保留介绍中的空格换行，正文按纯文本展示。
    echo "</span>";
}
?>
<h2><a href="../user/index.php">用户</a></h2>
<?php
foreach (Account::rankDirectory() as $account) {  // 按积分顺序列出账号，昵称重复时用括号里的登录名区分。
    echo "<a href=\"../user/detail.php?uid={$account['uid']}\">".h($account['nickname'])." (".h($account['usr']).")</a>\n";
}
?>
<h2><a href="../docs/index.php">文档</a></h2>
<a href="../user/login.php">登录</a>
<a href="../user/register.php">注册</a>
<h2><a href="../docs/map.php">地图</a></h2>
<?php echo 输出文本($SET['global_map']) /* 在动态目录之后追加管理员配置的地图说明。 */ ?>
</div>
</div>
<?php
    include_once(__DIR__.'/../include/footer.php');
?>