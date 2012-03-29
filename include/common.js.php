<?php
require_once("stdhead.php");
gethead(0,"","");
global $SETTINGS;
echo $cur;
$base64=pathconvert($cur,$SETTINGS['base']."include/base64.js");
?>

document.write('<script src="<?php echo $base64; ?>" language="javascript"></script>');