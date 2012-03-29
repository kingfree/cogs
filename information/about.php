<?php
require_once("../include/stdhead.php");
gethead(1,"","关于");
?>

<?php echo output_text($SETTINGS['global_about']) ?>

<?php
	include_once("../include/stdtail.php");
?>