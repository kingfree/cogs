<?php
require_once("../include/stdhead.php");
gethead(1,"","评论列表");

$p=new DataAccess();
$q=new DataAccess();

$sql="select comments.*,userinfo.nickname,userinfo.email,userinfo.uid,problem.pid,problem.probname from comments,userinfo,problem where userinfo.uid=comments.uid and problem.pid=comments.pid";
if ($_GET['key']!="")
$sql.=" and (problem.probname like '%{$_GET[key]}%' or problem.pid ='{$_GET[key]}' or problem.filename like '%{$_GET[key]}%' or comments.detail like '%{$_GET[key]}%')";
$sql.=" order by comments.stime desc";
$cnt=$p->dosql($sql);
$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
if(!$_GET['page']) {
    $_GET['page']=1;
    $st=0;
} else {
    if ($_GET[page]<1 || $_GET[page]>$totalpage)
        异常("页面错误！");
    else
        $st=(($_GET[page]-1)*$SET['style_pagesize']);
}
?>
<form id="search_prob" name="search_prob" method="get" action="">
搜索帖子全文
<input name="key" type="text" id="key" value="<?php echo $_GET['key'] ?>" />
<input name="sc" type="submit" id="sc" value="搜索"/>
</form>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&'); ?>
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
    <td class="CommentsU" rowspan=2>
<table>
<tr>
<td rowspan=2 width=30%><?=gravatar::showImage($d['email'], 64);?></td>
<td style="font-size:120%;">
<a href="<?=路径("mail/index.php")?>?toid=<?=$d['uid']?>" title="给<?=$d['nickname']?>发送信件"><span class="icon-envelope"></span></a>
<a href="<?php echo 路径("user/detail.php?uid={$uid}");?>"><b><?php echo $d['nickname'];?></b></a>
</td>
</tr>
<tr>
<td style="font-size:140%;"><a href="../problem/pdetail.php?pid=<?=$d['pid']?>"><?=$d['pid']?>: <?=$d['probname'] ?></a></td>
</tr>
</table>
    </td>
    <td colspan=2 class="CommentsK"><?php echo nl2br(sp2n(htmlspecialchars($d['detail'])))?></td>
  </tr>
  <tr>
	<td class="CommentsCode"><?php if ($d['showcode']){
	$sql="select sid from submit where uid='{$d['uid']}' and pid='{$pid}' order by accepted desc";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	?>
	<a href="../problem/submitdetail.php?id=<?php echo $e['sid'] ?>">查看该用户最后一次提交的代码</a>
	<?php } ?>
	</div>
    </td>
	<td  class="CommentsTime">发表时间：<?php echo date('Y-m-d H:i:s',$d['stime']);?></td>
  </di></tr>
</table>
<?php
	}
} else {
	echo "还没有人发表评论！";
}
	include_once("../include/stdtail.php");
?>

