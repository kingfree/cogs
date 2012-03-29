<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th width="6%" scope="col">CID</th>
    <th width="12%" scope="col">题目名</th>
    <th width="12%" scope="col">发表者</th>
    <th width="12%" scope="col">显示代码</th>
    <th width="13%" scope="col">发表时间</th>
    <th width="17%" scope="col">操作</th>
  </tr>
<?php
	$p=new DataAccess();
	$sql="select comments.*,userinfo.nickname,problem.probname from comments,userinfo,problem where userinfo.uid=comments.uid and problem.pid=comments.pid order by stime desc";
	
	if ($_GET[search]==1 && $_GET[key]!="")
	{
		$sql="select comments.*,userinfo.nickname,problem.probname from comments,userinfo,problem where userinfo.uid=comments.uid and problem.pid=comments.pid and (comments.detail like '%{$_GET[key]}%' or comments.cid ='{$_GET[key]}' or comments.pid ='{$_GET[key]}' or comments.uid ='{$_GET[key]}') order by stime desc";
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
    <td><?php echo $d['cid'] ?></td>
    <td><?php echo "<a href='../problem/pdetail.php?pid={$d[pid]}' target='_blank'>{$d['probname']}</a>"; ?></td>
    <td><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['nickname']}</a>" ;?></td>
    <td><?php if ($d['showcode']) echo "是"; else echo "否"; ?></td>
    <td><?php echo date('Y-m-d H:i:s', $d[stime]) ?></td>
    <td><a href="record/editcomments.php?action=edit&cid=<?php echo $d['cid'] ?>">修改</a> <a href="record/doeditcomments.php?action=del&cid=<?php echo $d['cid'] ?>&action=del">删除</a></td>
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
  <input name="fastgo" type="submit" id="fastgo" value="go"  class="Button"/>
  <input name="settings" type="hidden" id="settings" value="comments" />
</form>
<form id="form2" name="form2" method="get" action="">
  <p>搜索评论(CID PID UID 全文搜索)
    <input name="key" type="text" id="key"  class="InputBox"/>
    <input name="sc" type="submit" id="sc" value="搜索" class="Button" />
    <input name="search" type="hidden" id="search" value="1" />
    <input name="settings" type="hidden" id="settings" value="comments" />
  </p>
</form>