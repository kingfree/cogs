<?php
require_once("../include/stdhead.php");
gethead(1,"","题目评论");
?>

<?php
$p=new DataAccess();
$q=new DataAccess();
$pid=$_GET['pid'];
?>
<div class="Comments" id="Comments">
<?php
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
<table width="100%" border="0">
  <tr>
    <td width="25%" valign="top"><?php @showuser($d['uid'],$q) ?>
</td>
    <td width="75%" valign="top">
	<div class="CommentsK" ><?php echo nl2br(sp2n(htmlspecialchars($d['detail'])))?></div>
	<br />
	<div class="CommentsCode" >	发表时间
	<?php echo date('Y-m-d H:i:s',$d['stime']);?>
	<br />
	<?php if ($d['showcode']){ 
	$sql="select sid from submit where uid='{$d['uid']}' and pid='{$pid}' order by accepted desc";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
	?>
	<br /><a href="submitdetail.php?id=<?php echo $e['sid'] ?>">查看该用户最后一次提交的代码</a>
	<?php } ?>
	</div>
</td>
  </tr>
</table>

<?php
	echo "<hr class='Spliter'/>";
	}
}
else
{
	echo "还没有人发表评论！";
}
?>
</div>
</p>
<a href="pdetail.php?pid=<?php echo $pid ?>">[返回原题]</a></p>
<?php
if ($cnter)
{
?>
<form id="formcomm" name="formcomm" method="post" action="sendcomments.php">
<textarea name="detail" cols="100" rows="8" id="detail" class="TextArea"><?php echo $detail ?></textarea>
<input name="showcode" type="checkbox" id="showcode" value="1" <?php if ($sc){ ?> checked="checked" <?php } ?> />
<label for="showcode">显示你的代码</label><br />
<input name="Submit" type="submit" id="Submit" value="发表" class="LinkButton"/>
<input name="pid" type="hidden" id="pid" value="<?php echo $pid ?>" />
</form>
<?php
}
else
{
?>
	您还没有提交过这道题。
<?php 
}
?>

<?php
	include_once("../include/stdtail.php");
?>
