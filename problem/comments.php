<?php
require_once("../include/stdhead.php");
gethead(1,"","题目评论");

$p=new DataAccess();
$q=new DataAccess();
$pid=$_GET['pid'];

$sql="select comments.*,userinfo.* from comments,userinfo where userinfo.uid=comments.uid and comments.pid='{$_GET[pid]}' order by comments.stime asc";
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
    <td class="CommentsU" rowspan=2><?php @showuser($d['uid'],$q) ?></td>
    <td colspan=2 class="CommentsK"><?php echo nl2br(sp2n(htmlspecialchars($d['detail'])))?></td>
  </tr>
  <tr>
	<td class="CommentsCode"><?php if ($d['showcode']){
	$sql="select sid from submit where uid='{$d['uid']}' and pid='{$pid}' order by accepted desc";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	?>
	<a href="submitdetail.php?id=<?php echo $e['sid'] ?>">查看该用户最后一次提交的代码</a>
	<?php } ?>
    <a href="<?=路径("mail/index.php")?>?toid=<?=$d['uid']?>" title="给<?=$d['nickname']?>发送信件"><span class="icon-envelope"></span></a>
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
<?php
if ($cnter) {
?>
<form id="formcomm" name="formcomm" method="post" action="sendcomments.php">
<textarea name="detail" cols="100" rows="8" id="detail" class="TextArea"><?php echo $detail ?></textarea>
<br />
<a href="pdetail.php?pid=<?php echo $pid ?>">[返回原题]</a>
<input name="showcode" type="checkbox" id="showcode" value="1" <?php if ($sc){ ?> checked="checked" <?php } ?> />
<label for="showcode">显示你的代码</label>
<input name="Submit" type="submit" id="Submit" value="发表" />
<input name="pid" type="hidden" id="pid" value="<?php echo $pid ?>" />
</form>
<?php
} else {
?>
	您还没有提交过这道题。
<?php 
}
?>

<?php
	include_once("../include/stdtail.php");
?>
