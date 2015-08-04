<?
$p=new DataAccess();
$sql="select name,value from settings";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
	$d=$p->rtnrlt($i);
	$SET[$d['name']]=$d['value'];
}

$SET['cur']=$_SERVER['SCRIPT_FILENAME'];
$SET['global_root']=$cfg['dir_root'];
$str=$_SERVER['DOCUMENT_ROOT'];
if($str[strlen($str)-1] == '/') $hrl = ''; else $hrl = '/';
$SET['base']=$_SERVER['DOCUMENT_ROOT'].$hrl.$SET['global_root'];
$SET['URI']=base64_encode($_SERVER['REQUEST_URI']);

$cfg['Version']='2.8.2.1';
?>

