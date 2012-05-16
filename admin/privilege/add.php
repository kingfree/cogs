<?php
require_once("../../include/stdhead.php");
gethead(1,"修改权限","修改权限");
?>

<?
	$uid=(int) ($_POST['uid'] ? $_POST['uid'] : $_GET['uid']);
if(isset($_POST['do']) || (isset($_GET['uid']) && isset($_GET['pri']))) {
	$p=new DataAccess();
	$uid=(int) ($_POST['uid'] ? $_POST['uid'] : $_GET['uid']);
	$priv=(int) ($_POST['pri'] ? $_POST['pri'] : $_GET['pri']);
	$sql="insert into `privilege` values('$uid','$priv','1')";
	$cnt=$p->dosql($sql);
	echo "用户 $uid 已添加权限 ".array_search($priv, $pri)." ！";
}
?>
<form method=post>
	<b>为用户添加权限</b><br />
	用户编号：<input type=text size=10 name="uid" value="<?=$uid?>" /><br />
	用户权限：<select name="pri">
<?php
while(list($key, $val)=each($pri)) {
	if (isset($priv) && ($priv == $val)) {
		echo '<option value="'.$val.'" selected>'.$key.'</option>';
	} else {
		echo '<option value="'.$val.'">'.$key.'</option>';
	}
}
?></select><br />
	<input type='hidden' name='do' value='do' />
	<input type=submit value='添加权限' />
</form>
<?php
	include_once("../../include/stdtail.php");
?>
