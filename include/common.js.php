<?php
require_once("stdhead.php");
gethead(0,"","");
global $SET;
echo $cur;
$base64=pathconvert($cur,$SET['base']."include/base64.js");
?>

document.write('<script src="<?php echo $base64; ?>" language="javascript"></script>');