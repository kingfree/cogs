<?php
require_once("../include/stdhead.php");
gethead(0,"普通用户","");

$path = 路径("images/gravatar").'/';
$email = $_GET['email'];
for($i=16; $i<=200; $i++) {
	$file = $path.$email."s{$i}";
	while(file_exists($file))
		unlink($file);
}
echo '<script>document.location="../refresh.php?id=8"</script>';
?>
