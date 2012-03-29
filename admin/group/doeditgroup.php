<?php
require_once("../../include/stdhead.php");
gethead(0,"admin","");

if ($_REQUEST[action]=='add')
{
	$p=new DataAccess();
	$sql="insert into groups(gname,memo,adminuid,parent) values('{$_POST[gname]}','{$_POST[memo]}','{$_POST['adminuid']}','{$_POST['parent']}')";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=11"</script>';
}

if ($_REQUEST[action]=='edit')
{
	$p=new DataAccess();
	$sql="update groups set gname='{$_POST[gname]}',memo='{$_POST[memo]}',adminuid='{$_POST['adminuid']}',parent='{$_POST['parent']}' where gid={$_REQUEST[gid]}";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=11"</script>';
}
?>