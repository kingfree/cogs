<a class='admin_big' href='privilege/add.php'>添加权限</a>
<?php
	$p=new DataAccess();
	$sql="select privilege.*,userinfo.nickname from privilege,userinfo where userinfo.uid=privilege.uid order by uid,pri asc";
	
	$cnt=$p->dosql($sql);
	$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
	if (!isset($_GET[page])) 
	{
		$_GET[page]=1;
		$st=0;
	} else {
		if ($_GET[page]<1 || $_GET[page]>$totalpage)
		{
			echo "页错误！";
			$err=1;
		}
		else
		$st=(($_GET[page]-1)*$SET['style_pagesize']);
	}
    分页($cnt, $_GET['page'], '?settings=privilege&');
?>
<table cellspacing=0 cellpadding=4>
  <tr>
    <th>用户</th>
    <th>权限</th>
    <th>可用</th>
    <th>操作</th>
  </tr>
<?	if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><?php echo "<a href='../user/detail.php?uid={$d['uid']}' target='_blank'>{$d['nickname']}</a>" ;?></td>
    <td><?php echo array_search($d['pri'],$pri) ?></td>
    <td><?php if ($d['def']) echo "是"; else echo "否"; ?></td>
    <td><a href='privilege/delete.php?uid=<?=$d['uid']?>&pri=<?=$d['pri']?>'>删除</a></td>
<?php
	}
?>
</table>
