<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th width="5%" scope="col">UID</th>
    <th width="9%" scope="col">用户名</th>
    <th width="10%" scope="col">管理权限</th>
    <th width="12%" scope="col">昵称</th>
    <th width="9%" scope="col">真实姓名</th>
    <th width="10%" scope="col">所属分组</th>
    <th width="23%" scope="col">注册时间</th>
    <th width="22%" scope="col">操作</th>
  </tr>
<?php
	$p=new DataAccess();
	$sql="select userinfo.*,groups.gname from userinfo,groups where groups.gid=userinfo.gbelong order by uid asc";
	
	if ($_GET[search]==1 && $_GET[key]!="")
	{
		$sql="select userinfo.*,groups.gname from userinfo,groups where groups.gid=userinfo.gbelong and (nickname like '%{$_GET[key]}%' or uid ='{$_GET[key]}' or usr like '%{$_GET[key]}%' or realname like '%{$_GET[key]}%') order by uid asc";
	}
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
    <td><?php echo $d['uid'] ?></td>
    <td><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['usr']}</a>"; ?></td>
    <td><?php echo $STR[adminn][$d['admin']] ?></td>
    <td><?php echo $d['nickname'] ?></td>
    <td><?php echo $d['realname'] ?></td>
    <td><?php echo "<a href='../user/gdetail.php?gid={$d[gbelong]}' target='_blank'>{$d['gname']}</a>"; ?></td>
    <td><?php echo date('Y-m-d H:i:s',$d[regtime]) ?></td>
    <td><a href="user/edituser.php?uid=<?php echo $d['uid'] ?>">修改</a> <a href="user/doedituser.php?uid=<?php echo $d['uid'] ?>&action=del">删除</a></td>
  </tr>
<?php
	}
?>
</table>
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
  <input name="settings" type="hidden" id="settings" value="userlist" />
</form>
<form id="form2" name="form2" method="get" action="">
  <p>搜索用户(UID 用户名 用户昵称 真实姓名)
    <input name="key" type="text" id="key" class="InputBox" />
    <input name="sc" type="submit" id="sc" value="搜索" class="Button" />
    <input name="search" type="hidden" id="search" value="1" />
	<input name="settings" type="hidden" id="settings" value="userlist" />
  </p>
</form>