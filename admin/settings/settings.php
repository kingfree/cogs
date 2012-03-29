<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th scope="col">参数</th>
    <th scope="col">值</th>
    <th scope="col">修改</th>
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
    <td><a href="settings/editkey.php?sname=<?php echo $d['name'] ?>&method=text">文本</a> <a href="settings/editkey.php?sname=<?php echo $d['name'] ?>&method=html">HTML</a></td>
  </tr>
<?php
	}
?>
</table>
