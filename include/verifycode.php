<?php
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache"); 
session_start(); 

$text="0123456789abcdefghijklmnopqrstuvwxyz";

$img_width=60; //先定义图片的长、宽 
$img_height=24; 
srand(microtime() * 100000); //随机种子 
for($i=0;$i <4;$i++){ 
  $nmsg.=$text[rand(0,strlen($text)-1)]; //使用dechex()将生成的随机数转换成十六进制表示，就会出现 a,b,c,d,e,f等字母 
}
$_SESSION["IMGCODE"] = $nmsg; 


$mhimg = imageCreate($img_width,$img_height);    //创建图片 
ImageColorAllocate($mhimg, 255,255,200);        //图片底色 
$black = ImageColorAllocate($mhimg, 0,0,0);      //定义黑色 
//生成雪花背景。用·号代替雪花，并将它们的位置、颜色以及大小用随机显示，使它们看起来"杂乱无章、5颜6色" 
//为了区别文字，这里的颜色值不低于200 
for ($i=1; $i <=100; $i++){ 
imageString($mhimg,1,mt_rand(1,$img_width),mt_rand(1,$img_height),"." ,imageColorAllocate($mhimg,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255)));  
} 
//放置数字字母内容，1个1个地放，位置、大小、颜色随机 
//为了区别于背景，这里的颜色不超过200 
for ($i=0;$i <strlen($_SESSION["IMGCODE"]);$i++){ 
  imageString($mhimg, mt_rand(4,5),$i*$img_width/5+mt_rand(3,5),mt_rand(1,$img_height/3), $_SESSION["IMGCODE"][$i],imageColorAllocate($mhimg,mt_rand(0,255),mt_rand(0,125),mt_rand(0,255))); 
} 
ImageRectangle($mhimg,0,0,$img_width-1,$img_height-1,$black);//先成一黑色的矩形把图片包围 
Header("Content-type: image/jpg");    //定义输出文件格式 
ImagePng($mhimg);                    //输出图片png格式 
ImageDestroy($mhimg); 
?>
