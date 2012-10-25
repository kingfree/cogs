<?php
require_once("../include/header.php");
gethead(1,"","题目评论");
$LIB->hlighter();
$p=new DataAccess();
$uid=(int)$_SESSION['ID'];
$pid=(int)$_GET['pid'];
$aid=(int)$_GET['aid'];
if($pid<0) { $aid=-$pid; $pid=0; }
$cid=(int)$_GET['cid'];
if(!$uid)
    异常("非注册用户！", 取路径("problem/comments.php?pid={$pid}"));

if($pid) {
    $sql="select probname from problem where pid={$pid} limit 1";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
    } else
        异常("无此题目！", 取路径("problem/commentlist.php"));
} else if($cid) {
    $sql="select comments.*,problem.probname from comments,problem where comments.cid={$cid} and problem.pid=comments.pid limit 1";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
        $pid=$d['pid'];
        if($uid != $d['uid'])
            异常("不可更改他人评论！", 取路径("problem/comments.php?pid={$pid}"));
    } else
        异常("无此评论！", 取路径("problem/comments.php?pid={$pid}"));
} else if($aid) {
    $sql="select title from page where aid={$aid} limit 1";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
    } else
        异常("无此页面！", 取路径("page/index.php"));
    $pid=0;
} else {
    异常("什么也没找到！");
}
?>
<div class='container-fluid'>
<form method="post" action="sendcomments.php" class='form-horizontal'>
<div class='modal-header'>
<h3>发表
<? if($pid) { ?>
<a href="problem.php?pid=<?=$pid?>" target="_blank"><?=$d['probname']?></a>
<? } else if($aid) { ?>
<a href="../page/page.php?aid=<?=$aid?>" target="_blank"><?=$d['title']?></a>
<? } ?>
的评论</h3>
</div>
<div class='modal-body'>
<textarea name="detail" class='textarea' style='height:10em'><?php echo $d['detail'] ?></textarea>
<br />
<? if($pid) { ?>
    <label><input id="showcode" name="showcode" type="checkbox" value="1" <?php if($d['showcode']){ ?> checked="checked" <?php } ?> />允许查看你提交的代码</label>
<? } else { ?>
    <input name="showcode" type="hidden"  value="0" />
<? } ?>
</div>
<div class='modal-footer'>
<input name="pid" type="hidden" id="pid" value="<?php echo $pid?$pid:-$aid ?>" />
<input name="cid" type="hidden" id="cid" value="<?php echo $cid ?>" />
<? if($pid) { ?>
<a class='btn' href='comments.php?pid=<?=$pid?>'>返回评论列表</a>
<? } else if($aid) { ?>
<a class='btn' href='comments.php?aid=<?=$aid?>'>返回评论列表</a>
<? } ?>
<button type="submit" class='btn btn-primary'>发表评论</button>
</div>
</form>
</div>
<div class='clear page'>
<h4>BBCode 指南</h4>
<?
function showBBCode($txt, $str) {
    echo "<div class='bs-docs-example'><span class='after'>".$txt."</span>\n";
    echo nl2br(BBCode(sp2n(htmlspecialchars($str))))."</div>\n";
    echo "<pre class='prettyprint'>".htmlspecialchars($str)."</pre>\n";
}
showBBCode('常规样式', '[b]加粗[/b]、[i]倾斜[/i]和[u]下划线[/u]');
showBBCode('色彩与大小', '[size=25]大点的字体[/size] [color=red]红色字[/color]和[color=#d0d]自定义颜色[/color]');
showBBCode('超链接', '直接包含[url]http://cojs.tk/cogs[/url]，或者显示文本[url=http://cojs.tk/cogs]COGS[/url]');
showBBCode('图片', '[img]http://cojs.tk/cogs/style/cogs.png[/img]');
showBBCode('代码', '这个是[code]行内代码[/code]，你可以嵌入语言代码（c, cpp, pas）如
[code=cpp]#include <cstdio>
int main() {
    printf("hello, world\n");
    return 0;
}[/code]');
showBBCode('标签', '标签有六种：[label]默认[/label]、[label=success]成功[/label]、[label=warning]警告[/label]、[label=important]严重[/label]、[label=info]信息[/label]和[label=inverse]禁用[/label]。');
showBBCode('引用', '[quote]21世紀、世界の麻雀競技人口は一億人の大台を突破。
日本でも大規模な全国大会が毎年開催され、プロに直結する成績を残すべく高校麻雀部員達が覇を競っていた。
これはその頂点を目指す少女達の軌跡……。[/quote]');
?>
</div>

</div>

<?php
include_once("../include/footer.php");
?>
