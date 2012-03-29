<?php
require_once("../../include/stdhead.php");
gethead(0,"admin","");

if ($_REQUEST['action']=='add')
{
	$p=new DataAccess();
	$sql="insert into category(cname,memo) values('{$_POST[cname]}','{$_POST[memo]}')";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=12"</script>';
}

if ($_REQUEST['action']=='edit')
{
	$p=new DataAccess();
	$sql="update category set cname='{$_POST[cname]}',memo='{$_POST[memo]}' where caid={$_REQUEST[caid]}";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=12"</script>';
}
?>