<?php
require_once("../include/stdhead.php");
gethead(0,"sess","");

if ($_POST['showcode']==1)
	$scd=1;
else
	$scd=0;
$p=new DataAccess();
$sql="select * from comments where pid={$_POST['pid']} and uid={$_SESSION[ID]}";
$cnt=$p->dosql($sql);
$tm=time();
if (!$cnt) {
	$sql="insert into comments(pid,uid,detail,stime,showcode) values({$_POST['pid']},{$_SESSION[ID]},'{$_POST['detail']}',{$tm} ,{$scd})";
} else {
	$sql="update comments set detail='{$_POST['detail']}', stime={$tm} ,showcode={$scd} where pid={$_POST['pid']} and uid={$_SESSION[ID]}";
}
$p->dosql($sql);
echo '<script>document.location="../refresh.php?id=9&pid='.$_POST[pid].'"</script>';

?>