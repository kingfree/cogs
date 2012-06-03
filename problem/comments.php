<?php
require_once("../include/header.php");
gethead(1,"","题目评论");

$p=new DataAccess();
$q=new DataAccess();
$pid=$_GET['pid'];
?>
<div class='container'>
<a class='btn btn-success' href="#com" data-toggle='modal'>发表评论</a>

<?php
$sql="select comments.*,userinfo.nickname,userinfo.email,userinfo.uid,problem.pid,problem.probname from comments,userinfo,problem where userinfo.uid=comments.uid and problem.pid=comments.pid and comments.pid='{$_GET[pid]}'";
$sql.=" order by comments.stime asc";
$cnt=$p->dosql($sql);
$sended=0;
$cnter=0;
$sql="select * from submit where uid={$_SESSION[ID]} and pid='{$pid}'";
$cnter=$q->dosql($sql);
if ($cnt)
{
	for ($i=0;$i<$cnt;$i++)
	{
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
    <td colspan=2 class="CommentsK"><?php echo nl2br(sp2n(htmlspecialchars($d['detail'])))?></td>
  </tr>
  <tr>
	<td class="CommentsCode"><?php if ($d['showcode']){
	$sql="select sid from submit where uid='{$d['uid']}' and pid='{$pid}' order by subtime desc";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	?>
	<a href="../submit/submit.php?id=<?php echo $e['sid'] ?>">查看该用户最后一次提交的代码</a>
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
?>
</div>
<div id='com' class='modal hide fade in'>

<form method="post" action="sendcomments.php" class='form-horizontal'>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h2>发表评论</h2>
</div>
<div class='modal-body'>
<textarea name="detail" class='textarea'><?php echo $detail ?></textarea>
<br />
<label name="showcode"><input id="showcode" type="checkbox" value="1" <?php if ($sc){ ?> checked="checked" <?php } ?> />显示你的代码</label>
</div>
<div class='modal-footer'>
<input name="pid" type="hidden" id="pid" value="<?php echo $pid ?>" />
<button data-dismiss='modal' class='btn'>取消</button>
<button type="submit" class='btn btn-primary'>发表评论</button>
</div>
</form>
</div>
<?php
include_once("../include/footer.php");
?>
