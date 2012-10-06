<?php
require_once("../include/header.php");
gethead(1,"","站点地图");
$p=new DataAccess();
?>
<div class='row-fluid'>
<div class='span12'>
<div class='page'>

<h1>COGS 站点地图</h1>

<h2><a href="../problem/catelist.php">分类</a></h2>
<?php
$sql="select caid, cname from category order by cname asc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    echo "<a href=\"../problem/index.php?caid={$d['caid']}\">{$d['cname']}</a>\n";
}
?>
<h2><a href="../page/index.php">页面</a></h2>
<?php
$sql="select aid, title from page order by title asc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    echo "<a href=\"../page/page.php?aid={$d['aid']}\">{$d['title']}</a>\n";
}
?>
<h2><a href="../problem/index.php">题目</a></h2>
<?php
$sql="select pid, probname, filename from problem order by acceptcnt desc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    echo "<a href=\"../problem/problem.php?pid={$d['pid']}\">{$d['probname']} ({$d['filename']})</a>\n";
}
?>
<h2><a href="../contest/index.php">比赛</a></h2>
<?php
$sql="select comptime.cbid, comptime.ctid, compbase.cname, comptime.intro from comptime, compbase where comptime.cbid=compbase.cbid order by comptime.ctid desc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    echo "<span id='{$d['ctid']}'>";
    echo "<a href=\"../contest/problem.php?ctid={$d['ctid']}\">{$d['cname']}</a>\n";
    echo nl2br(sp2n(htmlspecialchars($d[intro])));
    echo "</span>";
}
?>
<h2><a href="../user/index.php">用户</a></h2>
<?php
$sql="select uid, usr, nickname from userinfo order by grade desc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    echo "<a href=\"../user/detail.php?uid={$d['uid']}\">{$d['nickname']} ({$d['usr']})</a>\n";
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
