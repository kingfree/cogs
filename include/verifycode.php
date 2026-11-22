<?php
// 注册表单的图形验证码，每次取图都会使上次输入失效。
session_start(['use_strict_mode'=>true, 'cookie_httponly'=>true, 'cookie_samesite'=>'Lax']);
$letters = '0123456789abcdefghijkmnpqrstuvwxyz';
$code = '';
for ($i=0; $i<4; $i++) $code .= $letters[random_int(0, strlen($letters)-1)];
$_SESSION['IMGCODE'] = $code;
header('Cache-Control: no-store');
header('Content-Type: image/png');
$image = imagecreatetruecolor(72, 28);
imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 220));
for ($i=0; $i<64; $i++) imagesetpixel($image, random_int(0,71), random_int(0,27), random_int(0, 0xffffff));
for ($i=0; $i<4; $i++) imagestring($image, 5, 4+$i*17, random_int(2,10), $code[$i], random_int(0, 0x555555));
imagepng($image);
