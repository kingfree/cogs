<?php
require_once("../../include/stdhead.php");
gethead(0,"admin","");

$p=new DataAccess();
$sql="update settings set value='{$_POST[value]}' where ssid={$_REQUEST[ssid]}";
$p->dosql($sql);
echo '<script>document.location="../../refresh.php?id=18"</script>';
?>