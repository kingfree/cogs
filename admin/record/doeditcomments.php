<?php
require_once("../../include/stdhead.php");
gethead(0,"管理评论","");

if ($_POST[showcode]==1) $sub=1; else $sub=0;

if ($_REQUEST[action]=='edit')
{
	$p=new DataAccess();
	$sql="update comments set showcode={$sub},detail='{$_POST[detail]}' where cid={$_REQUEST[cid]}";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=10"</script>';
}

if ($_REQUEST[action]=='del')
{
	$p=new DataAccess();
	$sql="delete from comments where cid={$_REQUEST[cid]}";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=10"</script>';
}
?>
