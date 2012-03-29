<?php
require_once("include/stdhead.php");
gethead(1,"admin","代码数据库");
$LIB->dpshhl();
$p=new DataAccess();
$q=new DataAccess();
$sql="select * from submit order by sid asc";
$cnt=$p->dosql($sql);
for($i=0; $i<$cnt; $i++)
{
	$d=$p->rtnrlt($i);
	if ($d[lang]==0) $ext="pas"; else
	if ($d[lang]==1) $ext="c"; else
	if ($d[lang]==2) $ext="cpp"; 
	if ($d['srcname']=="")
		$fp=fopen("{$SETTINGS['dir_source']}{$d[uid]}/{$d[filename]}.{$ext}","r");
	else
		$fp=fopen("{$SETTINGS['dir_source']}{$d[uid]}/{$d['srcname']}","r");
	if (is_resource($fp))
		$code=rfile($fp);
	fclose($fp);
    if(get_magic_quotes_gpc())
        $code=stripslashes($code);
    $source=mysql_real_escape_string($code);
    $sql1="INSERT INTO `code`(`sid`,`code`)VALUES('{$d['sid']}','$source')";
    $ok=$q->dosql($sql1);
    if($ok) echo $d['sid'];
}

	include_once("include/stdtail.php");
?>

