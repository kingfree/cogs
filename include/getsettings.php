<?
$p=new DataAccess();
$sql="select name,value from settings";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++)
{
	$d=$p->rtnrlt($i);
	$SETTINGS[$d['name']]=$d['value'];
}

$cfg['Version']='上古卷轴';
$SETTINGS['cur']=$_SERVER['SCRIPT_FILENAME'];
$SETTINGS['base']=$_SERVER['DOCUMENT_ROOT'].$SETTINGS['global_root'];
$SETTINGS['URI']=base64_encode($_SERVER['REQUEST_URI']);
?>
