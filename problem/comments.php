<?php
require_once("../include/header.php");
gethead(1,"","题目评论");

$p=new DataAccess();
$q=new DataAccess();
$pid=(int)$_GET['pid'];
?>
<div class='container-fluid'>
<? if($pid) { ?>
<a class='btn btn-danger' href="comment.php?pid=<?=$pid?>">发表评论</a>
<? } ?>
<?php
$sql="select comments.*,userinfo.nickname,userinfo.email,userinfo.uid,problem.pid,problem.probname from comments,userinfo,problem where userinfo.uid=comments.uid and problem.pid=comments.pid";
if($pid) $sql.=" and problem.pid=$pid";
if ($_GET['key']!="")
$sql.=" and (problem.probname like '%{$_GET[key]}%' or problem.pid ='{$_GET[key]}' or problem.filename like '%{$_GET[key]}%' or comments.detail like '%{$_GET[key]}%')";
$sql.=" order by comments.stime desc";
$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<div class='container-fluid'>
<form method="get" action="" class='center'>
搜索帖子全文
<input name="key" type="text" class='search-query input-medium' value='<?=$_GET['key']?>'/>
<button type="submit" class='btn'>搜索</button>
</form>
<?
if ($cnt) {
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'];$i++) {
		$d=$p->rtnrlt($i);
		if ($d['uid']==$_SESSION['ID'])
		{
			$sended=1;
			$detail=$d['detail'];
			$sc=$d['showcode'];
		}
?>
<table class="Comments">
  <tr>
    <td class="CommentsU" rowspan=2 valign='top'><table>
<tr>
<td rowspan=2 width='65px'><?=gravatar::showImage($d['email'], 64);?></td>
<td>
<a href="<?=路径("mail/index.php")?>?toid=<?=$d['uid']?>" title="给<?=$d['nickname']?>发送信件"><span class="icon-envelope"></span></a>
<a href="<?php echo 路径("user/detail.php?uid={$uid}");?>"><b><?php echo $d['nickname'];?></b></a>
</td>
</tr>
<tr>
<td style="font-size:140%;"><a href="../problem/problem.php?pid=<?=$d['pid']?>"><?=$d['pid']?>: <?=$d['probname'] ?></a></td>
</tr>
</table>
</td>
    <td colspan=4 class="CommentsK">
    <? if($_SESSION['ID']==$d['uid']) echo "<a href='comment.php?cid={$d['cid']}' class='pull-right btn btn-mini btn-warning'>修改</a>";?>
    <?php echo nl2br(sp2n(htmlspecialchars($d['detail'])))?>
    </td>
  </tr>
  <tr>
	<td class="CommentsCode"><?php if ($d['showcode']){
	$sql="select sid from submit where uid='{$d['uid']}' and pid='{$d['pid']}' order by subtime desc";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	?>
	<a href="../submit/code.php?id=<?=$e['sid']?>"><i class='icon icon-download'></i>查看该用户最后一次提交的代码</a>
	<?php } ?>
	</div>
    </td>
	<td class="CommentsTime">发表时间：<?php echo date('Y-m-d H:i:s',$d['stime']);?></td>
	<td class="CommentsTime">帖子编号：<?=$d['cid']?></td>
	<td class="CommentsTime">楼层编号：<?=($i+1)?></td>
  </di></tr>
</table>
<?php
	}
} else {
	echo "还没有人发表评论！";
}
?>
</div>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&'); ?>
<?php
include_once("../include/footer.php");
?>
