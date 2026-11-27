<?php
// 查询当前读者有权阅读的题目和文章讨论。
// problem/comments.php?pid=1 显示该题的讨论；?aid=1 显示该文章的讨论。
require_once(__DIR__.'/../include/header.php');
$pid=(int)($_GET['pid'] ?? 0);
$uid=(int)($_GET['uid'] ?? 0);
// 三类编号均可来自旧书签，缺省表示不限制对应条件。
$aid=(int)($_GET['aid'] ?? 0);
$ctid=(int)($_GET['ctid'] ?? 0);
gethead(1, '', '题目评论', $uid);              // 作者专页沿用该用户的背景。
require_once __DIR__.'/../include/hlighter.inc.php';
require_once __DIR__.'/../include/mathjax.inc.php';

?>
<div class='row-fluid'>
<form method="get" action="" class='form-search'>
<?php  if($_SESSION['ID']) { ?>
<?php  if($pid) { ?>
<a class='btn btn-danger' href="comment.php?pid=<?=$pid?>">发表评论</a>
<?php  } else if($aid) { ?>
<a class='btn btn-danger' href="comment.php?aid=<?=$aid?>">发表评论</a>
<?php  } else if($ctid) { ?>
<a class='btn btn-danger' href="comment.php?ctid=<?=$ctid?>">发表评论</a>
<?php  } ?>
<?php  } ?>
<?php  if($pid) { ?>
<a class='btn' href="problem.php?pid=<?=$pid?>">返回原题</a>
<?php  } else if($aid) { ?>
<a class='btn' href="../page/page.php?aid=<?=$aid?>">返回原页</a>
<?php  } else if($ctid) { ?>
<a class='btn' href="../contest/problem.php?ctid=<?=$ctid?>">返回比赛</a>
<?php  } ?>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=h($_GET['key'] ?? '')?>' placeholder='搜索评论'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
<?php  if($preview) { ?>
<a href="comments.php?show=yes" class='btn btn-inverse'>全部评论</a>
<?php  } ?>
</div>
<span>
题目PID
<input name="pid" type="number" value="<?=h($_GET['pid'] ?? '')?>" class='span1' />
用户UID
<input name="uid" type="number" value="<?=h($_GET['uid'] ?? '')?>" class='span1' />
<button type="submit" class='btn btn-primary'>检索</button>
</span>
</form>
<?php

$key = $_GET['key'] ?? '';
$show = $_GET['show'] ?? '';                         // 默认时间线只显示少量近况，具体题目或用户列出全部讨论。
$preview = !$show && !$pid && !$aid && !$uid;
$limit = $preview ? max(1, intdiv($SET['style_pagesize'], 6)) : null;
// 取列表时由 Comment 同时限定本账号可读的题目和文章。
$comments = Comment::search($key, $pid, $aid, $uid, $limit);
$total = count($comments);                            // 保留原下标作为楼层号，翻页不会从一楼重新编号。
$page = (int)($_GET['page'] ?? 1);
$offset = 检测页面($total, $page);
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php
if ($comments) {
    foreach (array_slice($comments, $offset, $SET['style_pagesize'], true) as $i=>$comment) {
?>
<tr>
<td valign='top' style="width: 14em;">
<a class="pull-left" href="<?php echo 路径("user/detail.php?uid={$comment['uid']}");?>">
<?=gravatar::showImage($comment['email'], 64);?>
</a>
<div style="margin-left:72px;">
<?php  if($_SESSION['ID']) { ?>
<a href="<?=路径("mail/index.php")?>?toid=<?=$comment['uid']?>" title="给<?=h($comment['nickname'])?>发送信件" class="pull-right"><span class="icon-envelope"></span></a>
<?php  } ?>
<a href="?uid=<?=$comment['uid']?>"
<?php if(Privilege::allows("查看用户")) echo "title='".h($comment['realname'])."'";?>>
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
<span class="pull-left">
<?php  if($comment['pid']) { ?>
<a href='problem.php?pid=<?=$comment['pid']?>' target='_blank'><span class='icon-share'></span></a>
<a href="?pid=<?=$comment['pid']?>">题目 <?=$comment['pid']?></a>
<?php  } else if($comment['aid']) { ?>
<a href='../page/page.php?aid=<?=$comment['aid']?>' target='_blank'><span class='icon-share'></span></a>
<a href="?aid=<?=$comment['aid']?>">页面 <?=$comment['aid']?></a>
<?php  } ?>
</span><?php if ($comment['showcode'] && $comment['latest_sid']) { ?>
<a href="../submit/code.php?id=<?=$comment['latest_sid']?>" target="_blank" title="<?=h($comment['latest_result'])?>"><i class='icon icon-download'></i><?=评测结果($comment['latest_result'], 30, true)?></a>
<?php } ?>
<div class="pull-right">
<span class="muted"><?php echo date('Y-m-d H:i:s',$comment['stime']);?></span>
<?php  if(!$preview) { ?>
<span><?=($i+1)?>楼</span>
<?php  } ?>
<?php  if($_SESSION['ID'] && $_SESSION['ID'] == $comment['uid']) { ?>
<a href='comment.php?cid=<?=$comment['cid']?>' class='btn btn-mini btn-warning'>修改</a>
<?php  } else if($_SESSION['ID']) { ?>
<a class='btn btn-mini btn-danger' href="comment.php?ccid=<?=$comment['cid']?>&pid=<?=$comment['pid']?>&aid=<?=$comment['aid']?>&user=<?=h($comment['nickname'])?>">回复</a>
<?php  } ?>
</div>
</div>
</td>
</tr>

<?php
	}
} else {
	echo "<div class='alert'>还没有人发表评论！</div>";
}
?>
<?php  if($preview) { ?>
<tr class="warning">
<td colspan=6><center>
<a href="comments.php?show=yes">全部评论</a>
</center></td></tr>
<?php  } ?>
</table>
<?php
if ($total > (int)$SET['style_pagesize']) {
    // 换页仍沿用当前讨论对象、作者和关键词。
    $query = http_build_query(['show'=>$show,'key'=>$key,'pid'=>$pid,'aid'=>$aid,'uid'=>$uid,'ctid'=>$ctid]);
    分页($total, $page, '?'.$query.'&');
}
?>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
