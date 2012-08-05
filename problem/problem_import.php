<?php
require_once("../include/header.php");
gethead(1,"修改题目","导入题目");
function writable($path){
	$ret=false;
	$fp=fopen($path."/testifwritable.tst","w");
	$ret=!($fp===false);
	fclose($fp);
	unlink($path."/testifwritable.tst");
	return $ret;
}
   $maxfile=min(ini_get("upload_max_filesize"),ini_get("post_max_size"));
$OJ_DATA=$cfg['testdata'];
?>
欲导入 FPS 数据，请确认上传文件大小小于 [<?php echo $maxfile?>] <br/>
or set upload_max_filesize and post_max_size in PHP.ini<br/>
if you fail on import big files[10M+],try enlarge your [memory_limit]  setting in php.ini.<br>
<?php 
    $show_form=true;
	   if(!writable($OJ_DATA)){
		   echo " You need to add  $OJ_DATA into your open_basedir setting of php.ini,<br>
					or you need to execute:<br>
					   <b>chmod 775 -R $OJ_DATA && chgrp -R www-data $OJ_DATA</b><br>
					you can't use import function at this time.<br>"; 
			$show_form=false;
	   }
	   if(!writable("../upload")){
		   echo "../upload is not writable, <b>chmod 770</b> to it.<br>";
		   $show_form=false;
	   }
	if($show_form){
?>
<br>
<form action='problem_import_xml.php' method=post enctype="multipart/form-data">
	<b>Import Problem:</b><br />
	<input type=file name=fps >
    <input type=submit value='Import'>
</form>
<?php 
   	}
?>
<br>

free problem set FPS-xml can be download at <a href=http://code.google.com/p/freeproblemset/downloads/list>FPS-Googlecode</a>
