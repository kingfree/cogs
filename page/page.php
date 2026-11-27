<?php
// 访问 page/page.php?aid=1 阅读文章。先校验文章阅读范围，再输出正文和关联讨论。
require_once(__DIR__.'/../include/header.php');
$aid = (int)($_GET['aid'] ?? 0);
$article = Page::forReader($aid); // 读取前先检查文章分组与阅读等级，缺失或不开放都返回 404。
if (!$article) { http_response_code(404); exit('页面不存在或未开放。'); } // 无权读者不能从状态码区分私有文章和不存在的编号。
$viewerId = (int)$_SESSION['ID'];
$canViewNames = Privilege::allows('查看用户'); // 控制讨论作者昵称旁的真实姓名提示。
$comments = Comment::search(pageId: $aid); // 文章被拒绝时，讨论及其作者也不会泄露。
gethead(1, '', $article['title']);
require_once __DIR__.'/../include/hlighter.inc.php';
require_once __DIR__.'/../include/mathjax.inc.php';
?>

<div class='row-fluid'>
<div class='page'>
<div class="problem tou">
<h1><?=h($article['title'])?>
<?php if(Privilege::allows('修改页面')) { ?>
<a href="editpage.php?action=edit&aid=<?=$article['aid']?>" title="修改页面 <?=h($article['title'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</h1>
由 <a href="../user/detail.php?uid=<?=$article['uid']; ?>" target="_blank"><?=h($article['nickname'])?></a> 在 <?=date('Y-m-d', $article['time']) ?> 创建
开放分组：<a href="../user/index.php?gid=<?=$article['gid'] ?>" target="_blank"><?=h($article['gname'])?></a>
上次编辑时间：<?=date('Y-m-d', $article['etime'])?>
</div>
<dl class='problem'>
<?=richText($article['text'])?>
</dl>
</div>
<div class="tou">
<table class='table table-condensed table-bordered fiexd'>
<tr><th colspan=5>
<a href="../problem/comments.php?aid=<?=$aid?>">关于 <b><?=shortname($article['title']); ?></b> 的讨论</a>
<?php  if ($viewerId) { ?>
<a href="../problem/comment.php?aid=<?=$aid?>" class="pull-right btn btn-mini btn-danger">发表评论</a>
<?php  } ?>
</th></tr>
<?php
foreach ($comments as $index => $comment) {
?>
<tr>
<td valign='top' style="width: 14em;">
<a class="pull-left" href="<?=路径("user/detail.php?uid={$comment['uid']}")?>">
<?=gravatar::showImage($comment['email'], 64);?>
</a>
<div style="margin-left:72px;">
<?php  if ($viewerId) { ?>
<a href="<?=路径("mail/index.php")?>?toid=<?=$comment['uid']?>" title="给<?=h($comment['nickname'])?>发送信件" class="pull-right"><span class="icon-envelope"></span></a>
<?php  } ?>
<a href="<?=路径("user/detail.php?uid={$comment['uid']}")?>"
<?php if ($canViewNames) echo "title='".h($comment['realname'])."'";?>>
<b><?=h($comment['nickname'])?></b>
</a>
<br />
积分：<?=$comment['grade']?><br />
提交：<?=$comment['accepted']?> / <?=$comment['submited']?>
</div>
</td>
<td>
<div>
<?php echo BBCode($comment['detail'])?>
<div class='tou muted wrap' style="text-align: right; width: 50%; position:relative; left: 50%;"><small>
<?php echo BBCode($comment['memo'])?>
</small></div>
</div>
<br />
<div style="vertical-align:text-bottom;">
<div class="pull-right">
<span class="muted"><?php echo date('Y-m-d H:i:s',$comment['stime']);?></span>
<span><?=($index+1)?>楼</span>
<?php  if ($viewerId && $viewerId === (int)$comment['uid']) { ?>
<a href='../problem/comment.php?cid=<?=$comment['cid']?>' class='btn btn-mini btn-warning'>修改</a>
<?php  } else if ($viewerId) { ?>
<a class='btn btn-mini btn-danger' href="../problem/comment.php?ccid=<?=$comment['cid']?>&aid=<?=$comment['aid']?>&user=<?=rawurlencode($comment['nickname'])?>">回复</a>
<?php  } ?>
</div>
</div>
</td>
</tr>
<?php
}
?>
</table>
</div>
</div>

<?php
include_once(__DIR__.'/../include/footer.php');
?>

