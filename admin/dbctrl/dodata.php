<?php


if ($_GET[action]=="reset") 
{
	require_once("../../include/stdlib.php");
	if ($_SESSION['admin']!=2)header("location: /{$SETTINGS['global_root']}error.php?id=7");
	$LIB->cls_dbctrl(); 
	$dbc=new DBControl;
	echo $STR[info][resetingdata];
	$dbc->deletetable();
	$dbc->createtable();
	echo '<script>document.location="../../refresh.php?id=6"</script>';
	exit;
}

require_once("../../include/stdhead.php");
gethead(0,"advadmin","");
$LIB->cls_dbctrl(); 
$dbc=new DBControl;

if ($_GET[action]=="backup") 
{
	echo "正在备份，请稍候";
	flush();
	
	$phpc="<?php\n";
	$p=new DataAccess();
	
	$result = mysql_list_tables($cfg[data_database]);
    while ($row = mysql_fetch_row($result)) 
	{
    	$table=$row[0];
		$phpc.="\n//表：{$table}\n";
		$sql="select * from {$table}";
		$phpc.='$sql="delete from '.$table.'";'."\n".'$p->dosql($sql);'."\n";
		$cnt=$p->dosql($sql);
		for ($i=0;$i<$cnt;$i++)
		{
			$d=$p->rtnrlt($i);
			$bksql="insert into {$table} values(";
			foreach($d as $k=>$v)
			{
				if (!is_numeric($k))
				{
					$bksql.="'".addslashes($v)."',";
				}
			}
			$bksql[strlen($bksql)-1]=")";
			$phpc.='$sql="'.$bksql.'";'."\n".'$p->dosql($sql);'."\n";
		}
    }
	
	$phpc.="\n?>";
	$file="{$SETTINGS['dir_databackup']}/$_GET[filename]";
	$fp=fopen($file,"w");
	fwrite($fp,$phpc);
	fclose($fp);
	echo "<meta http-equiv=refresh content='0; url=bkrsdata.php?action=backupsucc&file=".$_GET[filename]."'>"; 
}
if ($_GET[action]=="restore")
{
	echo "{$SETTINGS['dir_databackup']}/$_GET[filename]";
	if (file_exists("{$SETTINGS['dir_databackup']}/$_GET[filename]"))
	{
		echo "正在恢复，请稍候";
		flush();
		
		$p=new DataAccess();
		include("{$SETTINGS['dir_databackup']}/$_GET[filename]");
		echo "<meta http-equiv=refresh content='0; url=bkrsdata.php?action=restoresucc&file=".$_GET[filename]."'>"; 
		exit;
	}
}
?>