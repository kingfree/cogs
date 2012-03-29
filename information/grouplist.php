<?php
require_once("../include/stdhead.php");
gethead(1,"","分组列表");
?>
<?php 
	$p=new DataAccess();
	$q=new DataAccess();
	$sql="select groups.gid,groups.gname from groups_apply,groups where groups_apply.gid=groups.gid and uid='{$_SESSION['ID']}'";
	$cnt=$p->dosql($sql);
	if ($cnt)
	{
		$d=$p->rtnrlt(0);
?><p>你正在申请加入[<a href="userlist.php?gid=<?php echo $d['gid'] ?>"><?php echo $d['gname'] ?></a>]，请等待该组管理员的批准。</p>
<?php
	}
?>
<table width="100%" border="1">
  <tr>
    <th scope="col">分组名</th>
    <th scope="col">上级分组</th>
    <th scope="col">备注</th>
	<th scope="col">组管理员</th>
	<th scope="col">加入</th>
	<?php if ($_SESSION['admin']>0){ ?>
    <th scope="col">操作</th>
	<?php } ?>
  </tr>
<?php
	$sql="select groups.*,userinfo.uid,userinfo.nickname from groups,userinfo where groups.adminuid=userinfo.uid order by gname";
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
		if ($d['uid']==$_SESSION['ID'])
			$groupadmin=true;
?>
  <tr>
    <td><a href="userlist.php?gid=<?php echo $d['gid'] ?>"><?php echo $d['gname'] ?></a></td>
    <td><?php
if ($d['parent']!=-1)
{
	$sql="select * from groups where gid={$d['parent']}";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
?><a href="userlist.php?gid=<?php echo $e['gid'] ?>"><?php echo $e['gname'] ?></a><?php 
}
?></td>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
	<td><a href="../user/detail.php?uid=<?php echo $d['uid'] ?>"><?php echo $d['nickname'] ?></a></td>
	<td><a href="#" onclick="switchhide('join');document.getElementById('gid').value='<?php echo $d['gid'] ?>'; ">申请</a></td>
	<?php if ($_SESSION['admin']>0){ ?>
    <td><a href="../admin/group/editgroup.php?action=edit&gid=<?php echo $d['gid'] ?>">修改</a></td>
	<?php } ?>
  </tr>
<?php
	}
?>
</table>
<?php if ($_SESSION['admin']>0 || $groupadmin){ ?>
<p><a href="../admin/group/apply.php">管理申请加入</a></p>
<?php } ?>
<?php if ($_SESSION['admin']>0){ ?>
<p><a href="../admin/group/editgroup.php?action=add">添加新组</a></p>
<?php } ?>
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
<div id="join" style="width:562px; height:262px; display:none;" class="FloatDialog" align="center">
	<form action="joingroup.php" method="post" name="joingroup">
		<p><a href="#" onclick="switchhide('join');">关闭</a></p>
		<p>请输入你的加入原因，或你的个人信息，以通过组管理员的验证。</p>
		<textarea name="reason" cols="60" rows="4" class="TextArea"></textarea>
		<input name="gid" id="gid" type="hidden" value="0" />
		<p><input name="submit" type="submit" value="提交" class="Button"/></p>
	</form>
</div>
<?php
	include_once("../include/stdtail.php");
?>
