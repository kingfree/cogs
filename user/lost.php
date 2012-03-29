<?php
require_once("../include/stdhead.php");
gethead(1,"","找回密码");
?>

<p><a href="../">返回首页</a></p>
<?php
$p=new DataAccess();
if (empty($_POST[User])&&empty($_POST[ans]))
{
?>
<form id="Login" name="Login" method="post" action="lost.php">
  <p>第一步</p>
  <p>请输入您的用户名
    <input name="User" type="text" id="User" />
  </p>
  <p>
    <input name="next" type="submit" id="next" value="下一步" />
    </p>
</form>
<?php
}
else
{
	if (empty($_POST[ans]))
	{
?>
<form id="Login" name="Login" method="post" action="lost.php">
  <p>第二步</p>
  <p>
<?php 
		$sql="select pwdtipques from userinfo where usr='".$_POST['User']."'";
		$cnt=$p->dosql($sql);
		if ($cnt==0)
			echo '<script>document.location="../error.php?id=2"</script>';
		else
		{
			$d=$p->rtnrlt(0);
			echo "{$d[pwdtipques]}?";
?></p>
  <p>请输入您的密码提示问题的答案
    <input name="ans" type="text" id="ans" />
  </p>
  <p>
    <input name="next" type="submit" id="next" value="下一步" />
    <input name="User" type="hidden" id="User" value="<?php echo $_POST['User'] ?>" />
  </p>
</form>
<?php
		}
	}
	else
	{
		$sql="select pwdtipanshash from userinfo where usr='".$_POST['User']."'";
		$p->dosql($sql);
		$d=$p->rtnrlt(0);
		if ($d[pwdtipanshash]==encode($_POST[ans]))
		{
			$sql="update userinfo set pwdhash='".encode("") ."' where usr='".$_POST['User']."'";
			$p->dosql($sql);
			echo "密码已经被清空，请立刻登录并修改密码！";
		}
		else
		{
			echo '<script>document.location="../error.php?id=15"</script>';
		}

?>

<?php
	}
}
?>

<?php
	include_once("../include/stdtail.php");
?>