<?php
require_once("../include/stdhead.php");
gethead(1,"","分类列表");
?>
<?php
	$p=new DataAccess();
	$sql="select * from category order by cname";
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
?>
当前第<?php echo $_GET[page]?>页 共<?php echo $cnt?>条记录 共<?php echo $totalpage?>页 每页最多显示<?php echo $SETTINGS['style_pagesize'] ?>条记录
<?php if ($_SESSION['admin']>0){ ?>
<span style="text-align:right;background-color:#99FFCC; font-size:20px;"><a href="../admin/category/editcate.php?action=add">添加新分类</a></span></p>
<?php } ?>
<form id="form1" name="form1" method="get" action="">
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
  <input name="fastgo" type="submit" id="fastgo" value="GO" class="LinkButton" />
  <input name="settings" type="hidden" id="settings" value="category" />
</form>
<br />
<table border="1">
  <tr>
    <th scope="col">分类</th>
    <th scope="col">备注</th>
	<?php if ($_SESSION['admin']>0){ ?>
    <th scope="col">操作</th>
	<?php } ?>
  </tr>
<?php 
if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SETTINGS['style_pagesize'] ;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><a href="../problem/problist.php?caid=<?php echo $d['caid'] ?>"><?php echo $d['cname'] ?></a></td>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
	<?php if ($_SESSION['admin']>0){ ?>
    <td><a href="../admin/category/editcate.php?action=edit&caid=<?php echo $d['caid'] ?>">修改</a></td><?php } ?>
  </tr>
<?php
	}
?>
</table>

<?php
	include_once("../include/stdtail.php");

?>
