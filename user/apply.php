<?php
require_once("../include/header.php");
gethead(1,"","管理申请加入");
?>

<script type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='?page="+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
<?php
	$p=new DataAccess();
	$sql="select gid from groups where adminuid={$_SESSION['ID']}";
	$cnt=$p->dosql($sql);
	if (!$cnt)
		exit;
	$d=$p->rtnrlt($i);
	$agid=$d['gid'];
	
	$sql="select groups_apply.*,groups.gname,userinfo.nickname,userinfo.realname from groups_apply,groups,userinfo where groups_apply.gid=groups.gid and userinfo.uid=groups_apply.uid";

	if ($agid>=0 && !($_SESSION['admin']>0))
		$sql.=" and groups_apply.gid={$agid}";

	$sql.=" order by gid asc";
	
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
?>
<p>当前第<strong><?php echo $_GET['page']?></strong>页 共<strong><?php echo $cnt?></strong>条记录 共<strong><?php echo $totalpage?></strong>页 每页最多显示<strong><?php echo $SET['style_pagesize'] ?></strong>个申请</p>

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
	else
		echo "上一页";
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
	else
		echo " 下一页";
}
?>
	去
	<select name="select" class="InputBox" onchange="MM_jumpMenu('parent',this,0)">
		<option value="" selected="selected">---</option>
<?php
for ($i=1;$i<=$totalpage;$i++)
{
?>
	  <option value="<?php echo $i;?>">第<?php echo $i;?>页</option>
<?php
}
?>
    </select>
	<input name="caid" type="hidden" id="caid" value="<?php echo $_GET['caid'] ?>" />
</p>
</form>

<p>
<table width="100%" border="1" >
  <tr>
    <th scope="col">UID</th>
    <th scope="col">用户</th>
    <th scope="col">申请加入分组</th>
    <th scope="col">原因</th>
    <th scope="col">真实姓名</th>
    <th scope="col">操作</th>
  </tr>
<?php
	if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><?php echo $d['uid'] ?></td>
    <td><a href="../user/detail.php?uid=<?php echo $d['uid'] ?>" target="_blank"><?php echo $d['nickname'] ?></a></td>
    <td><a href="../user/index.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></td>
    <td><a href="#" onclick="switchhide('<?php echo $i ?>');">查看</a></td>
    <td><?php echo $d['realname'] ?></td>
    <td><a href="doapply.php?action=accept&uid=<?php echo $d['uid'] ?>&gid=<?php echo $d['gid'] ?>">接受</a> <a href="doapply.php?action=refuse&uid=<?php echo $d['uid'] ?>&gid=<?php echo $d['gid'] ?>">拒绝</a></td>
  </tr>
<div style="position: absolute; width:562px; height:262px; left:50%; top:40%; margin-left:-280px; margin-top:-100px;float:left;background:#FFFFFF; border:solid #000000 1px; display:none; padding:10px;" id="<?php echo $i ?>">
	<a href="#" onclick="switchhide('<?php echo $i ?>');">关闭</a>
	<pre>
<?php echo $d['reason'] ?>
	</pre>
</div>
<?php
	}
?>
</table>
</p>
<?php
	include_once("../include/footer.php");
?>