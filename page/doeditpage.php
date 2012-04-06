<?php
require_once("../include/stdhead.php");
//date_default_timezone_set("Asia/Shanghai");
if ($_REQUEST[action]=='add') {
	$p=new DataAccess();
	$sql="insert into page(title,`force`,`text`,`time`,etime,uid,`group`) values('{$_POST[title]}','{$_POST[force]}','".mysql_real_escape_string($_POST[text])."','".time()."','".time()."','{$_SESSION[ID]}','{$_POST['group']}')";
	$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$aid=$d['aid'];
}

if ($_REQUEST[action]=='edit') {
	$p=new DataAccess();
	$sql="update page set title='{$_POST[title]}',`force`={$_POST[force]}, etime=".time().",`text`='".mysql_real_escape_string($_POST[text])."',`group`='{$_POST['group']}' where aid={$_REQUEST[aid]}";
	$p->dosql($sql);
	$aid=$_REQUEST[aid];
}

if ($_REQUEST[action]=='del') {
	$p=new DataAccess();
	$sql="delete from page where aid={$_REQUEST[aid]}";
	$p->dosql($sql);
	$aid=0;
}
echo "<script>document.location=\"../refresh.php?id=23&aid={$aid}\"</script>";
?>

