<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th width="6%" scope="col">GRID</th>
    <th width="8%" scope="col">名称</th>
    <th width="12%" scope="col">地址</th>
    <th width="6%" scope="col">状态</th>
    <th width="6%" scope="col">版本</th>
    <th width="8%" scope="col">评测次数</th>
    <th width="8%" scope="col">优先级</th>
    <th width="24%" scope="col">备注</th>
    <th width="16%" scope="col">操作</th>
  </tr>
<?php
	$LIB->func_socket();

	$p=new DataAccess();
	$sql="select * from grader";
	$cnt=$p->dosql($sql);
	$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
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
		$st=(($_GET[page]-1)*$SET['style_pagesize']);
	}
	if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++)
	{
		$d=$p->rtnrlt($i);
		$s['action']="state";
		$tmp=httpsocket($d['address'],$s);
		$tmp=array_decode($tmp);

		if ($tmp==array())
		{
			$tmp['name']="无法连接";
			$tmp['state']="未知";
			$tmp['ver']="未知";
			$tmp['cnt']="未知";
		}
?>
  <tr>
    <td><?php echo $d['grid'] ?></td>
    <td><?php echo $tmp['name'] ?></td>
    <td><?php echo $d['address'] ?></td>
    <td><?php echo $tmp['state'] ?></td>
    <td><?php echo $tmp['ver'] ?></td>
    <td><?php echo $tmp['cnt'] ?></td>
    <td><?php echo $d['priority'] ?></td>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
    <td><a href="grader/editgrader.php?action=edit&grid=<?php echo $d['grid'] ?>">修改</a> <a href="grader/doeditgrader.php?action=start&grid=<?php echo $d['grid'] ?>">启动</a> <a href="grader/doeditgrader.php?action=stop&grid=<?php echo $d['grid'] ?>">关闭</a></td>
  </tr>
<?php
	}
?>
</table>
<p><a href="grader/editgrader.php?action=add">添加新评测机</a></p>
<p>当前第<?php echo $_GET[page]?>页 共<?php echo $cnt?>条记录 共<?php echo $totalpage?>页 每页最多显示<?php echo $SET['style_pagesize'] ?>条记录</p>
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
  <input name="settings" type="hidden" id="settings" value="grader" />
</form>