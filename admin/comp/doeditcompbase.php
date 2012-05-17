<?php
require_once("../../include/stdhead.php");
gethead(0,"修改比赛","");

if ($_REQUEST[action]=='add')
{
	$p=new DataAccess();
	@$cons=implode(":",$_POST[cons]);
	$sql="insert into compbase(cname,contains,ouid) values('{$_POST[cname]}','{$cons}',{$_SESSION[ID]})";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=14"</script>';
}

if ($_REQUEST[action]=='edit')
{
	$p=new DataAccess();
	@$cons=implode(":",$_POST[cons]);
	$sql="update compbase set cname='{$_POST[cname]}',contains='{$cons}' where cbid={$_REQUEST[cbid]}";	
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=14"</script>';
}
?>
