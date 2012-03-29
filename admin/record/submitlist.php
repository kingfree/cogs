<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th width="5%" scope="col">SID</th>
    <th width="8%" scope="col">详细信息</th>
    <th width="12%" scope="col">题目名</th>
    <th width="8%" scope="col">用户昵称</th>
    <th width="8%" scope="col">真实姓名</th>
    <th width="5%" scope="col">得分</th>
    <th width="11%" scope="col">测试点</th>
    <th width="8%" scope="col">通过</th>
    <th width="9%" scope="col">代码</th>
    <th width="8%" scope="col">提交次数</th>
    <th width="26%" scope="col">上次提交时间</th>
  </tr>
<?php
	$p=new DataAccess();
	$sql="select submit.sid,submit.pid,submit.uid,submit.lang,submit.actime,submit.submitcnt,submit.score,userinfo.nickname,userinfo.realname,submit.subtime,problem.probname,submit.result from submit,userinfo,problem where submit.pid=problem.pid and submit.uid=userinfo.uid order by submit.subtime desc";
	
	if ($_GET[search]==1 && $_GET[key]!="")
	{
		$sql="select submit.sid,submit.pid,submit.uid,submit.lang,submit.actime,submit.submitcnt,submit.score,userinfo.nickname,userinfo.realname,submit.subtime,problem.probname,submit.result from submit,userinfo,problem where submit.pid=problem.pid and submit.uid=userinfo.uid and (problem.probname like '%{$_GET[key]}%' or problem.pid ='{$_GET[key]}' or problem.filename like '%{$_GET[key]}%' or userinfo.uid ='{$_GET[key]}' or userinfo.nickname like '%{$_GET[key]}%' or userinfo.usr like '%{$_GET[key]}%' or submit.sid='{$_GET[key]}')  order by submit.subtime desc";
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
    <td><?php echo $d['sid'] ?></td>
    <td><?php echo "<a href='../problem/submitdetail.php?id={$d['sid']}'>查看</a>" ?></td>
    <td><?php echo "<a href='../problem/pdetail.php?pid={$d[pid]}' target='_blank'>{$d['probname']}</a>"; ?></td>
    <td><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['nickname']}</a>"; ?></td>
    <td><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['realname']}</a>"; ?></td>
    <td><?php echo $d['score'] ?></td>
    <td><?php echo $d['result'] ?></td>
    <td><?php echo $d[actime]?"通过":"未通过"; ?></td>
    <td><?php echo "{$STR[lang][$d['lang']]}" ?></td>
    <td><?php echo $d['submitcnt'] ?></td>
    <td><?php echo date('Y-m-d H:i:s',$d[subtime]); ?></td>
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
  <input name="fastgo" type="submit" id="fastgo" value="go" class="Button" /><input name="settings" type="hidden" id="settings" value="submit" /></form>
<form id="form2" name="form2" method="get" action="">
  <p>搜索题目(SID PID UID 题目名 文件名 用户名 用户昵称)
    <input name="key" type="text" id="key"  class="InputBox"/>
    <input name="sc" type="submit" id="sc" value="搜索" class="Button" />
    <input name="search" type="hidden" id="search" value="1" />
    <input name="settings" type="hidden" id="settings" value="submit" />
  </p>
</form>