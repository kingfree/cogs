<?php
require_once("../../include/stdhead.php");
gethead(0,"admin","");

if ($_GET[action]=="del") 
{
	$p=new DataAccess();
	$sql="select admin,uid from userinfo where uid={$_GET[uid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
	if (($_SESSION['admin']==1 && $d[admin]==2)|| $d[uid]==1)
	{
		echo '<script>document.location="../../error.php?id=10"</script>';
		exit;
	}
	$sql="delete from userinfo where uid={$_GET[uid]}";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=5"</script>';
}
else
if ($_POST[comefrom]!="edituser") 
echo '<script>document.location="../../error.php?id=8"</script>';
else
{
	$p=new DataAccess();
	if ($_POST['reset'] != "reset")
        $sql="update userinfo set nickname='{$_POST[nickname]}' ,readforce={$_POST[readforce]} ,email='{$_POST[email]}', admin={$_POST[admin]},grade={$_POST[grade]},memo='{$_POST[memo]}',realname='{$_POST[realname]}',gbelong={$_POST[gbelong]} where uid={$_GET[uid]}";
	else
        $sql="update userinfo set nickname='{$_POST[nickname]}' ,readforce={$_POST[readforce]} ,email='{$_POST[email]}', admin={$_POST[admin]},grade={$_POST[grade]},memo='{$_POST[memo]}',pwdhash='".encode("")."',realname='{$_POST[realname]}',gbelong={$_POST[gbelong]} where uid={$_GET[uid]}";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=5"</script>';
}
?>
