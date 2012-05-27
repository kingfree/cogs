<?php
require_once("../include/stdhead.php");
gethead(8,"普通用户","");

$txt="";
$path = 路径("images/gravatar").'/';
$email = $_GET['email'];
for($i=16; $i<=200; $i++) {
	$file = $path.$email."s{$i}";
	if(file_exists($file)) {
		unlink($file);
        gravatar::showImage($d['email'], $i);
        $txt .= "<p>清除 ".$file." ， 重建 $i 中...</p>";
    }
}


提示($txt."重建头像缓存完成！", 取路径("user/panel.php"));

?>
