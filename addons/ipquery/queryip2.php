<?php
header('Content-Type:text/html;charset=GB2312');

echo httpsocket("http://www.kmwhedu.net/test/ajax_ipscan/ajax_ipscan.php",$_SERVER['QUERY_STRING']);

//echo "1`{$location['ip']}`{$location['country']}{$location['area']}```{$location['country']}`34.76`113.65";


function httpsocket($url,$query) 
{
	$url = str_replace('http://', '', $url);
	$path = explode('/', $url);
	$url = $path[0];
	unset($path[0]);
	$path = '/' . implode('/', $path)."?".$query;
	
	$fp = @fsockopen($url, 80, $errno, $errstr, 6);
	
	if(!$fp) 
	{
		return "error";
	}
	
	
	$out = 'GET ' . $path . ' HTTP/1.1' . "\r\n";
	$out .= 'Host: ' . $url . "\r\n";
	$out .= 'Connection: close' . "\r\n";
	$out .= 'Content-Length: ' . strlen($q) . "\r\n";
	$out .= 'Content-Type: application/x-www-form-urlencoded; charset=iso-8859-1' . "\r\n\r\n";
	
	fwrite($fp, $out);
	$buf="";
	while (!feof($fp))
		$buf .= fgets($fp,128);
	
	fclose($fp);
	
	$arr=explode("GB2312",$buf);
	
	return substr($arr[1],4);
}

?>