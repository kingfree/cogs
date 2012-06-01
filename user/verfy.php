<?php
if ($_GET['action']=='verfy')
{
	require_once("../include/header.php");
	gethead(0,"","");
	$p=new DataAccess();
	$sql="select * from userinfo where uid='{$_GET['uid']}'";
	$p->dosql($sql);
	$d=$p->rtnrlt(0);
	if ($d['pwdhash']==$_GET['code'])
	{
		$sql="update userinfo set admin='0' where uid={$_GET['uid']}";
		$p->dosql($sql);
	}
	header("location: /{$SET['global_root']}");
}
require_once("../include/header.php");
gethead(1,"verfy","用户验证");
$LIB->get_userinfo($_SESSION['ID']);
?>

<p>您还未被验证，如果您的邮箱填写正确，我们已经向您的邮箱发送过了验证邮件，请点击邮件中的链接以通过验证。如果您还没有收到，或者始终无法收到，请尝试修改邮箱并重发验证邮件。</p>
<p>Yahoo邮箱可能无法收到，推荐使用Gmail。</p>
<form name="Editverfy" method="post" action="editverfy.php">
  
  <p>注册时填写的邮箱
    <input name="email" type="text" id="email" value="<?php echo $_SESSION['email'] ?>" class="InputBox">
</p>
  <p>
    <input type="submit" name="Submit" value="修改邮箱并重发验证邮件" class="Button">
  </p>
</form>

<?php
	include_once("../include/footer.php");
?>