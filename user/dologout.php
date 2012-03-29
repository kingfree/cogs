<?php
require_once("../include/stdhead.php");
gethead(0,"","");

setcookie("cojs_login",$d['pwdhash'], time()-7776000);
setcookie("User",$_POST['User'], time()-7776000);
echo '<script>document.location="../refresh.php?id=3"</script>';
?>