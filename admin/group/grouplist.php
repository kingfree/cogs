<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th width="10%" scope="col">GID</th>
    <th width="16%" scope="col">组名</th>
    <th width="24%" scope="col">备注</th>
    <th width="21%" scope="col">操作</th>
  </tr>
<?php
	$p=new DataAccess();
	$sql="select * from groups";
	$cnt=$p->dosql($sql);
	$totalpage=(int)(($cnt-1)/$SETTINGS['style_pagesize'])+1;
	if (!isset($_GET[page])) 
	{
		$_GET[page]=1;
		$st=0;
	}
	else 
	{
		if ($_GET[page]<1 || $_GET[page]>$totalpage)
		{
			echo "页错误！";
			$err=1;
		}
		else
		$st=(($_GET[page]-1)*$SETTINGS['style_pagesize']);
	}
	if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SETTINGS['style_pagesize'] ;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><?php echo $d['gid'] ?></td>
    <td><a href="../user/gdetail.php?gid=<?php echo $d['gid'] ?>"><?php echo $d['gname'] ?></a></td>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
    <td><a href="group/editgroup.php?action=edit&gid=<?php echo $d['gid'] ?>">修改组</a></td>
  </tr>
<?php
	}
?>
</table>
<p><a href="group/editgroup.php?action=add">添加新组</a></p>
<p>当前第<?php echo $_GET[page]?>页 共<?php echo $cnt?>条记录 共<?php echo $totalpage?>页 每页最多显示<?php echo $SETTINGS['style_pagesize'] ?>条记录</p>
<form id="form1" name="form1" method="get" action="">
  <p>

    <?php 
if (!$err)
{
	if ($_GET[page]>1)
	{
		$lp=$_GET[page]-1;
		
		$url="?";
		foreach($_GET as $k=>$v)
		{
			if ($k!='page')
				$url.="{$k}={$v}&";
		}
		$url.="page=$lp";
		
		echo "<a href='$url'>上一页</a>";
	}
	if ($_GET[page]!=$totalpage)
	{
		$lp=$_GET[page]+1;
		
		$url="?";
		foreach($_GET as $k=>$v)
		{
			if ($k!='page')
				$url.="{$k}={$v}&";
		}
		$url.="page=$lp";
		
		echo " <a href='$url'>下一页</a>";	
	}
}
?>
    去第
    <input name="page" type="text" id="page" size="4"  class="InputBox" />
    页 
  <input name="fastgo" type="submit" id="fastgo" value="go" class="Button" />
  <input name="settings" type="hidden" id="settings" value="grouplist">
</form>