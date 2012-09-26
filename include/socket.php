<?php
function httpsocket($url,$query) {
    $url = str_replace('http://', '', $url);
    $path = explode('/', $url);
    $url = $path[0];
    unset($path[0]);
    $path = '/' . implode('/', $path);
    $fp = @fsockopen($url, 80, $errno, $errstr, 6);
    if(!$fp) return "error ($errno)";
    $q  =  "query=".array_encode($query);
    $out = 'POST ' . $path . ' HTTP/1.1' . "\r\n";
    $out.= 'Host: ' . $url . "\r\n";
    $out.= 'Connection: close' . "\r\n";
    $out.= 'Content-Length: ' . strlen($q) . "\r\n";
    $out.= 'Content-Type: application/x-www-form-urlencoded; charset=iso-8859-1' . "\r\n\r\n";
    $out.= $q . "\r\n";
    fwrite($fp, $out);
    $buf="";
    while(!feof($fp))
        $buf .= fgets($fp, 128);
    fclose($fp);
    $arr=explode("<return>",$buf);
    return $arr[1];
}
?>
