<?php
// 访问 page/page.php?aid=1 阅读文章。先校验文章阅读范围，再输出正文和关联讨论。
require_once("../include/header.php");
$aid = (int)$_GET['aid'];
$d = Page::forReader($aid);
if (!$d) { http_response_code(404); exit('页面不存在或未开放。'); }
gethead(1, '', $d['title']);
require_once __DIR__.'/../include/hlighter.inc.php';
require_once __DIR__.'/../include/mathjax.inc.php';
?>

<div class='row-fluid'>
<div class='page'>
<div class="problem tou">
<h1><?=h($d['title'])?>
<?php if(Privilege::allows('修改页面')) { ?>
<a href="editpage.php?action=edit&aid=<?=$d['aid']?>" title="修改页面 <?=h($d['title'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</h1>
由 <a href="../user/detail.php?uid=<?=$d['uid']; ?>" target="_blank"><?=h($d['nickname'])?></a> 在 <?=date('Y-m-d', $d['time']) ?> 创建
开放分组：<a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=h($d['gname'])?></a>
上次编辑时间：<?=date('Y-m-d', $d['etime'])?>
</div>
<dl class='problem'>
<?=richText($d['text'])?>
</dl>
</div>
<div class="tou">
<table class='table table-condensed table-bordered fiexd'>
<tr><th colspan=5>
<a href="../problem/comments.php?aid=<?=$aid?>">关于 <b><?=shortname($d['title']); ?></b> 的讨论</a>
<?php  if($_SESSION['ID']) { ?>
<a href="../problem/comment.php?aid=<?=$aid?>" class="pull-right btn btn-mini btn-danger">发表评论</a>
<?php  } ?>
</th></tr>
<?php
$comments = Comment::search('', 0, $aid, 0, null);

foreach ($comments as $i => $d) {
?>
<tr>
<td valign='top' style="width: 14em;">
<a class="pull-left" href="<?php echo 路径("user/detail.php?uid={$d['uid']}");?>">
<?=gravatar::showImage($d['email'], 64);?>
</a>
<div style="margin-left:72px;">
<?php  if($_SESSION['ID']) { ?>
<a href="<?=路径("mail/index.php")?>?toid=<?=$d['uid']?>" title="给<?=h($d['nickname'])?>发送信件" class="pull-right"><span class="icon-envelope"></span></a>
<?php  } ?>
<a href="<?php echo 路径("user/detail.php?uid={$d['uid']}");?>"
<?php if(Privilege::allows("查看用户")) echo "title='".h($d['realname'])."'";?>>
<b><?=h($d['nickname'])?></b>
</a>
<br />
积分：<?=$d['grade']?><br />
提交：<?=$d['accepted']?> / <?=$d['submited']?>
</div>
</td>
<td>
<div>
<?php echo BBCode($d['detail'])?>
<div class='tou muted wrap' style="text-align: right; width: 50%; position:relative; left: 50%;"><small>
<?php echo BBCode($d['memo'])?>
</small></div>
</div>
<br />
<div style="vertical-align:text-bottom;">
<div class="pull-right">
<span class="muted"><?php echo date('Y-m-d H:i:s',$d['stime']);?></span>
<?php  if($_GET['show'] || $pid || $aid || $uid) { ?>
<span><?=($i+1)?>楼</span>
<?php  } ?>
<?php  if($_SESSION['ID'] && $_SESSION['ID'] == $d['uid']) { ?>
<a href='../problem/comment.php?cid=<?=$d['cid']?>' class='btn btn-mini btn-warning'>修改</a>
<?php  } else if($_SESSION['ID']) { ?>
<a class='btn btn-mini btn-danger' href="../problem/comment.php?ccid=<?=$d['cid']?>&aid=<?=$d['aid']?>&user=<?=h($d['nickname'])?>">回复</a>
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
include_once("../include/footer.php");
?>

