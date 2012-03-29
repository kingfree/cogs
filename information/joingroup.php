<?php
require_once("../include/stdhead.php");
gethead(0,"sess","");

$uid=$_SESSION['ID'];
$gid=(int) $_POST['gid'];
$reason=$_POST['reason'];

$p=new DataAccess();

$sql="select groups_apply.gid from groups_apply where groups_apply.uid={$uid}";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$sql="UPDATE `groups_apply` SET `gid` = '{$gid}',`reason`='{$reason}' WHERE uid ={$uid}";
}
else
{
	$sql="INSERT INTO `groups_apply` (`uid` ,`gid` ,`reason`) VALUES ('{$uid}', '{$gid}', '{$reason}' );";
}

$p->dosql($sql);

echo '<script>document.location="../refresh.php?id=20"</script>';

?>