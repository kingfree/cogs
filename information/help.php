<?php
require_once("../include/stdhead.php");
gethead(1,"","帮助");
?>
<?php echo output_text($SETTINGS['global_help']) ?>
<?php
	include_once("../include/stdtail.php");
?>