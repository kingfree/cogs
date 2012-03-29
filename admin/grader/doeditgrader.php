<?php
require_once("../../include/stdhead.php");
gethead(0,"admin","");
$LIB->func_socket();

if ($_POST['enabled']==1) $enabled=1; else $enabled=0;

if ($_REQUEST[action]=='add')
{
	$p=new DataAccess();
	$sql="insert into grader(address,priority,enabled,memo) values('{$_POST['address']}','{$_POST['priority']}','{$enabled}','{$_POST[memo]}')";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=17"</script>';
}

if ($_REQUEST['action']=='edit')
{
	$p=new DataAccess();
	$sql="update grader set address='{$_POST['address']}',priority='{$_POST['priority']}',enabled='{$enabled}',memo='{$_POST['memo']}' where grid={$_REQUEST[grid]}";
	$p->dosql($sql);
	echo '<script>document.location="../../refresh.php?id=17"</script>';
}

if ($_REQUEST['action']=='start')
{
	$p=new DataAccess();
	$sql="select address from grader where grid={$_GET[grid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$s['action']="start";
	httpsocket($d['address'],$s);
	echo '<script>document.location="../../refresh.php?id=17"</script>';
}

if ($_REQUEST['action']=='stop')
{
	$p=new DataAccess();
	$sql="select address from grader where grid={$_GET[grid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$s['action']="shutdown";
	httpsocket($d['address'],$s);
	echo '<script>document.location="../../refresh.php?id=17"</script>';
}
?>