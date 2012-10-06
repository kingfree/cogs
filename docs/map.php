<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"; ?>
<?php
require_once("../include/header.php");
$p=new DataAccess();
$cogs="http://cojs.tk/cogs";
?>
<urlset xmls="http://www.sitemaps.org/schemas/sitemap/0.9">
<loc><?=$cogs?>/index.php</loc>

<loc><?=$cogs?>/problem/catelist.php</loc>
<?php
$sql="select caid, cname from category order by cname asc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    echo "<loc>{$cogs}/problem/index.php?caid={$d['caid']}</loc>\n";
}
?>
<loc><?=$cogs?>/page/index.php</loc>
<?php
$sql="select aid, title from page order by title asc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    if($i % $SET['style_pagesize'] == 0) {
        $j = $i / $SET['style_pagesize'] + 1;
        echo "<loc>{$cogs}/page/index.php?page={$j}</loc>\n";
    }
    echo "<loc>{$cogs}/page/page.php?aid={$d['aid']}</loc>\n";
}
?>
<loc><?=$cogs?>/problem/index.php</loc>
<?php
$sql="select pid, probname, filename from problem order by acceptcnt desc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    if($i % $SET['style_pagesize'] == 0) {
        $j = $i / $SET['style_pagesize'] + 1;
        echo "<loc>{$cogs}/problem/index.php?page={$j}</loc>\n";
    }
    echo "<loc>{$cogs}/problem/problem.php?pid={$d['pid']}</loc>\n";
    echo "<loc>{$cogs}/problem/comments.php?pid={$d['pid']}</loc>\n";
}
?>
<loc><?=$cogs?>/contest/index.php</loc>
<?php
$sql="select comptime.cbid, comptime.ctid, compbase.cname, comptime.intro from comptime, compbase where comptime.cbid=compbase.cbid order by comptime.ctid desc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    if($i % $SET['style_pagesize'] == 0) {
        $j = $i / $SET['style_pagesize'] + 1;
        echo "<loc>{$cogs}/contest/index.php?page={$j}</loc>\n";
    }
    echo "<loc>{$cogs}/contest/problem.php?ctid={$d['ctid']}</loc>\n";
}
?>
<loc><?=$cogs?>/user/index.php</loc>
<?php
$sql="select uid, usr, nickname from userinfo order by grade desc";
$cnt=$p->dosql($sql);
for($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    if($i % $SET['style_pagesize'] == 0) {
        $j = $i / $SET['style_pagesize'] + 1;
        echo "<loc>{$cogs}/user/index.php?page={$j}</loc>\n";
    }
    echo "<loc>{$cogs}/user/detail.php?uid={$d['uid']}</loc>\n";
}
?>
<loc><?=$cogs?>/docs/index.php</loc>
<loc><?=$cogs?>/user/login.php</loc>
<loc><?=$cogs?>/user/register.php</loc>
</urlset>

<?php //echo 输出文本($SET['global_map']) ?>
