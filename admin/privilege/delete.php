<?php
require_once("../../include/stdhead.php");
gethead(1,"admin","删除权限");
?>

<?
if(isset($_GET['uid']) && isset($_GET['pri'])){
	$p=new DataAccess();
	$uid=(int) ($_POST['uid'] ? $_POST['uid'] : $_GET['uid']);
	$priv=(int) ($_POST['pri'] ? $_POST['pri'] : $_GET['pri']);
	$sql="delete from `privilege` where uid=$uid and pri=$priv";
	$cnt=$p->dosql($sql);
	echo "用户 $uid 已删除权限 ".array_search($priv, $pri)." ！";
}
?>
	<script>history.go(-1);</script>
<?php
	include_once("../../include/stdtail.php");
?>
