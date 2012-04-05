<?php
require_once("../include/stdhead.php");
gethead(0,"","");

$LIB->cls_reg();
$rc=new RegisterCheck;

if (!$rc->check_reg($_POST,$_SESSION["IMGCODE"]))
{
	echo '<script>document.location="../error.php?id=4"</script>';
	exit;
}
$p=new DataAccess();

$sql="select * from userinfo where usr='".$_POST['usr']."'";
$cnt=$p->dosql($sql);
if ($cnt==0)
{
	$sql="insert into userinfo(uid,usr,nickname,readforce,admin,regtime,pwdhash,pwdtipques,pwdtipanshash,memo,portrait,realname,email) values (0, '{$_POST[usr]}','{$_POST[nickname]}',2,0, ". time() .",'". encode($_POST[pwd]) ."' ,'{$_POST[passwordtip]}' , '". encode($_POST[passwordtipans]) ."', '{$_POST[memo]}',". mt_rand(1,$SET['style_portrait']) .",'{$_POST['realname']}','{$_POST['email']}')";
	$p->dosql($sql);
	$sql="select * from userinfo where usr='".$_POST['usr']."'";
	$cnt2=$p->dosql($sql);
	if ($cnt2==1)
	{
		$d=$p->rtnrlt(0);
	}
	else
	{
		echo '<script>document.location="../error.php?id=6"</script>';
	}
	echo '<script>document.location="../refresh.php?id=4"</script>';
}
else
{	
	echo '<script>document.location="../error.php?id=5"</script>';
}
?>
