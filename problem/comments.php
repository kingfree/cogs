<?php
require_once("../include/header.php");
gethead(1,"","题目评论");

$p=new DataAccess();
$q=new DataAccess();
$pid=(int)$_GET['pid'];
?>
<div class='row-fluid'>
<form method="get" action="" class='form-search'>
<? if($pid && $_SESSION['ID']) { ?>
<a class='btn btn-danger' href="comment.php?pid=<?=$pid?>">发表评论</a>
<? } ?>
<? if($pid) { ?>
<a class='btn' href="problem.php?pid=<?=$pid?>">返回原题</a>
<? } ?>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=$_GET['key']?>' placeholder='搜索评论'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
<a href="comments.php" class='btn'>全部评论</a>
</div>
</form>
<?php
$sql="select comments.*,userinfo.nickname,userinfo.email,userinfo.uid,problem.pid,problem.probname from comments,userinfo,problem where userinfo.uid=comments.uid and problem.pid=comments.pid ";
if($pid) $sql.=" and problem.pid=$pid";
if ($_GET['key']!="")
$sql.=" and (problem.probname like '%{$_GET[key]}%' or problem.pid ='{$_GET[key]}' or problem.filename like '%{$_GET[key]}%' or comments.detail like '%{$_GET[key]}%')";
if($pid) $sql.=" order by comments.cid asc"; else $sql.=" order by comments.stime desc";
$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<?
if ($cnt) {
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'];$i++) {
		$d=$p->rtnrlt($i);
		if ($d['uid']==$_SESSION['ID']) {
			$sended=1;
			$detail=$d['detail'];
			$sc=$d['showcode'];
		}
?>
<table class='Comments table table-condensed table-bordered fiexd'>
  <tr>
    <td class="CommentsU" rowspan=2 valign='top'><table>
<tr>
<td rowspan=2 width='65px'><?=gravatar::showImage($d['email'], 64);?></td>
<td>
<a href="<?=路径("mail/index.php")?>?toid=<?=$d['uid']?>" title="给<?=$d['nickname']?>发送信件"><span class="icon-envelope"></span></a>
<a href="<?php echo 路径("user/detail.php?uid={$d['uid']}");?>"><b><?php echo $d['nickname'];?></b></a>
</td>
</tr>
<tr>
<td>
<? if(!$pid) { ?>
<a href="?pid=<?=$d['pid']?>"><?=$d['pid']?>: <?=$d['probname'] ?></a>
<? } ?>
</td>
</tr>
</table>
</td>
    <td colspan=4 class="CommentsK">
    <? if($_SESSION['ID']==$d['uid']) echo "<a href='comment.php?cid={$d['cid']}' class='pull-right btn btn-mini btn-warning'>修改</a>";?>
    <?php echo nl2br(sp2n(htmlspecialchars($d['detail'])))?>
    </td>
  </tr>
  <tr>
	<td class="CommentsCode" style="width: 6em;"><?php if ($d['showcode']){
	$sql="select sid from submit where uid='{$d['uid']}' and pid='{$d['pid']}' order by subtime desc";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	?>
	<a href="../submit/code.php?id=<?=$e['sid']?>"><i class='icon icon-download'></i>查看代码</a>
	<?php } ?>
	</div>
    </td>
	<td class="CommentsTime">发表时间：<?php echo date('Y-m-d H:i:s',$d['stime']);?></td>
	<td class="CommentsTime" style="width: 8em;">帖子编号：<?=$d['cid']?></td>
	<td class="CommentsTime" style="width: 8em;">楼层编号：<?=($i+1)?></td>
  </di></tr>
</table>
<?php
	}
} else {
	echo "<div class='alert'>还没有人发表评论！</div>";
}
?>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&'); ?>
</div>
<?php
include_once("../include/footer.php");
?>
