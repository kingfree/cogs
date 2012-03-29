<?php
require_once("../../include/stdhead.php");
gethead(0,"","");

$gid=$_REQUEST['gid'];
$uid=$_REQUEST['uid'];

$p=new DataAccess();
$sql="select gid from groups where adminuid={$_SESSION['ID']}";
$cnt=$p->dosql($sql);
if (!$cnt)
	exit;
$d=$p->rtnrlt($i);
$agid=$d['gid'];

if (!($_SESSION['admin']>0) && $agid!=$gid)
	exit;	

if ($_REQUEST['action']=='accept')
{
	$sql="update userinfo set gbelong={$gid} where uid={$uid}";
	$p->dosql($sql);
}

$sql="delete from groups_apply where uid={$uid}";

$p->dosql($sql);

echo '<script>document.location="../../refresh.php?id=21"</script>';
?>