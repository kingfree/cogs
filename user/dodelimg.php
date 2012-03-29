<?php
require_once("../include/stdhead.php");
gethead(0,"sess","");

global $SETTINGS,$STR;
$portrait=$SETTINGS['base']."images/gravatar";
$path = pathconvert($SETTINGS['cur'],$portrait).'/';
$email = $_GET['email'];
for($i=16; $i<=200; $i++) {
	$file = $path.$email."s{$i}dmm";
	if (file_exists($file))
		unlink($file);
}
echo '<script>document.location="../refresh.php?id=8"</script>';
?>
