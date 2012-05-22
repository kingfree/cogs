<table>
  <tr>
    <th width=20%>参数</th>
    <th>值</th>
    <th width=10%>修改</th>
  </tr>
<?php
	$p=new DataAccess();
	$sql="select * from settings";
	$cnt=$p->dosql($sql);
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><?php echo $d['name'] ?></td>
    <td><?php echo $d['value'] ?></td>
    <td>
    <a href="settings/editkey.php?sname=<?php echo $d['name'] ?>&method=text">纯文本</a>
    <a href="settings/editkey.php?sname=<?php echo $d['name'] ?>&method=html">富文本</a>
    </td>
  </tr>
<?php
	}
?>
</table>
