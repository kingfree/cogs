<?php
// 显示发表评论或修改本人评论的表单。对已有评论，从记录读取原题或原文章。
// 打开 problem/comment.php?pid=1 写题目评论，或用 ?cid=4 编辑自己的第 4 号评论。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","题目评论");
require_once __DIR__.'/../include/hlighter.inc.php';
require_once __DIR__.'/../include/mathjax.inc.php';
$uid = (int)$_SESSION['ID'];
$pid = (int)($_GET['pid'] ?? 0);                   // 新评论从地址取得题目或文章编号。
$aid = (int)($_GET['aid'] ?? 0);
$cid = (int)($_GET['cid'] ?? 0);
// 游客可以阅读讨论，需要登录后才能发表或修改。
if (!$uid) 异常('非注册用户！', 取路径("problem/comments.php?pid=$pid"));

$comment = null;
if ($cid) {
    $comment = Comment::byId($cid)[0] ?? null;       // 已发表的评论由记录确定归属，表单不能换题。
    if (!$comment) 异常('无此评论！', 取路径("problem/comments.php?pid=$pid"));
    $pid = (int)$comment['pid'];
    $aid = (int)$comment['aid'];                    // 新旧页面共用同一入口，返回对应的原讨论。
    if ($uid !== (int)$comment['uid']) {
        异常('不可更改他人评论！', 取路径('problem/comments.php?'.($pid ? 'pid='.$pid : 'aid='.$aid)));
    }
}
$topic = $pid ? Problem::forReader($pid) : Page::forReader($aid);     // 原题或文章必须仍对当前账号开放。
if (!$topic) { http_response_code(404); exit('讨论对象不存在或未开放。'); }  // 用户即便保留了旧表单地址也需能阅读评论原文。
?>
<div class='container-fluid'>
<form method="post" action="sendcomments.php" class='form-horizontal' id="tijiao"><?=formTokenField()?>
<div class='modal-header'>
<h3><?=($cid)?"修改":"发表"?> 
<?php  if($pid) { ?>
<a href="problem.php?pid=<?=$pid?>" target="_blank"><?=h($topic['probname'])?></a>
<?php  } else if($aid) { ?>
<a href="../page/page.php?aid=<?=$aid?>" target="_blank"><?=h($topic['title'])?></a>
<?php  } ?>
的评论</h3>
</div>
<div class='modal-body'>
<textarea name="detail" class='textarea' style='height:10em'>
<?php
if(!empty($_GET['user'])) echo "回复 @".h($_GET['user'])." : \n";
echo h($comment['detail'] ?? '');
?></textarea>
<br />
<?php  if($pid) { ?>
    <label><input id="showcode" name="showcode" type="checkbox" value="1" <?php if(!empty($comment['showcode'])){ ?> checked="checked" <?php } ?> />允许查看你提交的代码</label>
<?php  } else { ?>
    <input name="showcode" type="hidden" value="0" />
<?php  } ?>
</div>
<div class='modal-footer'>
<input name="pid" type="hidden" value="<?=$pid?>" />
<input name="aid" type="hidden" value="<?=$aid?>" />
<input name="cid" type="hidden" value="<?=$cid?>" />
<?php  if($pid) { ?>
<a class='btn' href='comments.php?pid=<?=$pid?>'>返回评论列表</a>
<?php  } else if($aid) { ?>
<a class='btn' href='comments.php?aid=<?=$aid?>'>返回评论列表</a>
<?php  } ?>
<button type="submit" class='btn btn-primary'>发表评论</button>
</div>
</form>
</div>
<div class='clear page'>
<h4>BBCode 指南</h4>
<?php
function showBBCode($txt, $str) {
    echo "<div class='bs-docs-example'><span class='after'>".$txt."</span>\n";
    // 上方展示渲染结果，下方保留可复制的原始写法。
    echo BBCode($str)."</div>\n";
    echo "<pre class='prettyprint'>".htmlspecialchars($str)."</pre>\n";
}
showBBCode('常规样式', '[b]加粗[/b]、[i]倾斜[/i]和[u]下划线[/u] [size=25]大点的字体[/size] [color=red]红色字[/color]和[color=#d0d]自定义颜色[/color]');
showBBCode('超链接和@', '直接包含[url]http://cojs.tk/cogs[/url]，或者显示文本[url=http://cojs.tk/cogs]COGS[/url]。还可以用 UID @524 、用户昵称 @王者自由 、姓名或其他称号 @[Kingfree]KF 来提及别人，注意后面要有空格来分隔，目前被提及人还不能收到提示。');
$emoticons = '';                                  // 表情编号保留两位，例如 (bgm01)。
for ($i=1; $i<=100; $i++) $emoticons .= sprintf('(bgm%02d)', $i);
showBBCode('图片', '原大小：[img]http://cojs.tk/cogs/style/cogs.png[/img]，缩放到宽度：[img width=80]http://cojs.tk/cogs/style/cogs.png[/img]。
表情暂时有'.$emoticons);
// 代码示例故意保留缩进和换行，方便读者直接复制到编辑框。
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
// 同时给出行内和独立公式，覆盖 MathJax 对齐与矩阵的写法。
showBBCode('数学式子', '行内公式：$ a^2 + b^2 = c^2 $, $ \sqrt{2} $, $O(n \lg n)$, $ f[i][j] = \min\{f[i][j], f[i][k] + f[k][j]\} $。
行间公式：\[ \sum_{i=1 下标}^{n 上标} {i 求和} = 1 + 2 + \cdots 省略号 + n = \frac{n(1 + n)}{2 分数}\]
矩阵和括号：\[
{\bf{A}} = \left(\begin{array}{lll}
1 & 1 & 1\\\\
0 & 1 & 0\\\\
1 & 0 & 0\\\\
\end{array}\right)
\quad 空格 \quad
f_{i,j} = \min\left\{\begin{array}{l}
f_{i,j}\\\\
f_{i,k} + f_{k,j}
\end{array}\right.
\]
');
?>
</div>

</div>

<?php
include_once(__DIR__.'/../include/footer.php');
?>
