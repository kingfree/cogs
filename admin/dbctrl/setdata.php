<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td width="88%" valign="top"><strong>数据库信息</strong>
    <p><?php 
$link=mysql_connect($cfg[data_server],$cfg[data_uid],$cfg[data_pwd]);
printf ("MySQL PHP端版本: %s<br>", mysql_get_client_info());
printf ("MySQL 服务器端版本: %s<br>", mysql_get_server_info());
printf ("MySQL 服务器端信息: %s<br>", mysql_get_host_info());
printf ("MySQL 协议信息: %s<br>", mysql_get_proto_info());
echo "<table border='1' style='BORDER-COLLAPSE:collapse' bordercolor=#FF0000  cellspacing=0 cellpadding=4><tr><td>进程ID</td><td>IP:端口</td><td>连接数据库</td><td>命令</td><td>时间</td></tr>";
$result = mysql_list_processes($link);
while ($row = mysql_fetch_assoc($result))
    echo "<tr><td>$row[Id]</td><td>$row[Host]</td><td>$row[db]</td><td>$row[Command]</td><td> $row[Time]</td></tr>";
$thread_id = mysql_thread_id($link);
if ($thread_id)
    printf ("</table>本次链接进程ID:%d<p><strong>表信息</strong><p>", $thread_id);
	
$db_name=$cfg[data_database];
//获取当前数据库中的数据列表
$result_table=mysql_list_tables($db_name,$link);
for($j=0;$j<mysql_num_rows($result_table);$j++)
{
	$table_name=mysql_tablename($result_table,$j);
	echo "表：[".$table_name."]<br/>字段：| ";
	//获取当前数据表中的属性列表
	$result_field=mysql_list_fields($db_name,$table_name,$link);
	for ($k=0;$k<mysql_num_fields($result_field);$k++)
	{
		$field_name=mysql_field_name($result_field,$k);
		echo $field_name." | ";
	}
	echo "<br/>";
}

mysql_free_result ($result); 
?></td>
    <td width="12%" valign="top"><table width="100%" border="0">
      <tr>
        <td><strong>数据库操作</strong></td>
      </tr>
      <tr>
        <td><a href="javascript:if(confirm('<?php echo $STR[info][resetdata]; ?>'))window.location='dbctrl/dodata.php?action=reset'" ><?php echo $STR[admin][resetdata]; ?></a></td>
      </tr>
      <tr>
        <td><a href="dbctrl/bkrsdata.php?action=backup"><?php echo $STR[admin][backupdata]; ?></a></td>
      </tr>
      <tr>
        <td><a href="dbctrl/bkrsdata.php?action=restore"><?php echo $STR[admin][restoredata]; ?></a></td>
      </tr>
    </table></td>
  </tr>
</table>