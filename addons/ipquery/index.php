<?php
require_once("../../include/stdhead.php");
gethead(1,"sess","IP查询");
$ip=$_GET['ip'];
if ($ip=="")
{
	$ip=$_SERVER['REMOTE_ADDR'];
}
?>

<script language="javascript" src="http://api.51ditu.com/js/search.js"></script>
<script language="javascript" src="http://api.51ditu.com/js/maps.js"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAkY4dJeNp1SiV8avIz-LR-hQ4vqi7liJwFpkJ7xvMzR5myMA6xBTb4VK-f6Wf6-uRbHzD2kyCx7xU_w" type="text/javascript"></script>
<script src="ajax_ipscan.js"></script>
<div align="center">
	<fieldset id="mess_box">
		<span id="user_info" style="width: 100%"></span>
		<span class="fcolor"> 输IP/域名: </span> 
		<input style="ime-mode:disabled" id="scan_ip" type="text" size="20" maxlength="50" onKeyDown="if(event.keyCode==13) GetScanInfo();" />
		<input name="query" type="button" class="button" id="query" onClick="GetScanInfo();" value="查询"/>
		<p>
			<div id="scan_info" style="width: 760px; font-size:18px;"></div>
		</p>
		<p>
			<div id="wymap" style="width: 900px; height: 320px"></div>
			<div id="gmap" style="width: 900px; height: 320px"></div>
		</p>
	</fieldset>
</div>
<script>
var user_id="1";
document.getElementById("scan_ip").value="<?php echo $ip ?>";
GetScanInfo();
</script>

<?php
require_once("../../include/stdtail.php");
?>
