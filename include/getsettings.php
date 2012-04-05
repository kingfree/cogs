<?
$p=new DataAccess();
$sql="select name,value from settings";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
	$d=$p->rtnrlt($i);
	$SET[$d['name']]=$d['value'];
}

$cfg['Version']='1.8.0.0';
$SET['cur']=$_SERVER['SCRIPT_FILENAME'];
$SET['base']=$_SERVER['DOCUMENT_ROOT'].$SET['global_root'];
$SET['URI']=base64_encode($_SERVER['REQUEST_URI']);
?>
