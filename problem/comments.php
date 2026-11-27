<?php
// 查询当前读者有权阅读的题目和文章讨论。
// problem/comments.php?pid=1 显示该题的讨论；?aid=1 显示该文章的讨论。
require_once("../include/header.php");
$pid=(int)$_GET['pid'];
$uid=(int)$_GET['uid'];
$aid=(int)$_GET['aid'];
$ctid=(int)$_GET['ctid'];
if($uid)
    gethead(1,"","题目评论", $uid);
else
    gethead(1,"","题目评论");
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
<?php  if(!($_GET['show'] || $pid || $aid || $uid)) { ?>
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

$limit = empty($_GET['show']) && !$pid && !$aid && !$uid ? max(1, intdiv($SET['style_pagesize'], 6)) : null;
$comments = Comment::search($_GET['key'] ?? '', $pid, $aid, $uid, $limit);
$cnt = count($comments);
$st=检测页面($cnt, ($_GET['page'] ?? 1));
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php
if($cnt) {
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'];$i++) {
		$d=($comments[$i] ?? false);
		if ($d['uid']==$_SESSION['ID']) {
			$sended=1;
			$detail=$d['detail'];
			$sc=$d['showcode'];
		}
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
<a href="?uid=<?=$d['uid']?>"
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
<span class="pull-left">
<?php  if($d['pid']) { ?>
<a href='problem.php?pid=<?=$d['pid']?>' target='_blank'><span class='icon-share'></span></a>
<a href="?pid=<?=$d['pid']?>">题目 <?=$d['pid']?></a>
<?php  } else if($d['aid']) { ?>
<a href='../page/page.php?aid=<?=$d['aid']?>' target='_blank'><span class='icon-share'></span></a>
<a href="?aid=<?=$d['aid']?>">页面 <?=$d['aid']?></a>
<?php  } ?>
</span><?php if ($d['showcode']) {
	$e = ['sid'=>$d['latest_sid'], 'result'=>$d['latest_result']];
?>
<a href="../submit/code.php?id=<?=$e['sid']?>" target="_blank" title="<?=$e['result']?>"><i class='icon icon-download'></i><?=评测结果($e['result'], 30, true)?></a>
<?php } ?>
<div class="pull-right">
<span class="muted"><?php echo date('Y-m-d H:i:s',$d['stime']);?></span>
<?php  if($_GET['show'] || $pid || $aid || $uid) { ?>
<span><?=($i+1)?>楼</span>
<?php  } ?>
<?php  if($_SESSION['ID'] && $_SESSION['ID'] == $d['uid']) { ?>
<a href='comment.php?cid=<?=$d['cid']?>' class='btn btn-mini btn-warning'>修改</a>
<?php  } else if($_SESSION['ID']) { ?>
<a class='btn btn-mini btn-danger' href="comment.php?ccid=<?=$d['cid']?>&pid=<?=$d['pid']?>&aid=<?=$d['aid']?>&user=<?=h($d['nickname'])?>">回复</a>
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
<?php  if(!($_GET['show'] || $pid || $aid || $uid)) { ?>
<tr class="warning">
<td colspan=6><center>
<a href="comments.php?show=yes">全部评论</a>
</center></td></tr>
<?php  } ?>
</table>
<?php
if ($cnt > (int)$SET['style_pagesize'])
    分页($cnt, ($_GET['page'] ?? 1), '?show='.$_GET['show'].'?key='.$_GET['key'].'&');
?>
</div>
<?php
include_once("../include/footer.php");
?>
