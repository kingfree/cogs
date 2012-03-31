<?php
require_once("../include/stdhead.php");
gethead(1,"","用户列表");
?>

<script type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='?key=<?php echo $_GET['key'] ?>&rank=<?php echo $_GET['rank'] ?>&page="+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
<?php
	$p=new DataAccess();
	$q=new DataAccess();
	$sql="select userinfo.*,groups.gname from userinfo,groups where groups.gid=userinfo.gbelong ";

	if ($_GET['gid']=="")
		$_GET['gid']=0;
	$sql1="select * from groups where gid={$_GET['gid']}";
	$cnt=$p->dosql($sql1);
	$d=$p->rtnrlt(0);
	
	$subgroup=$LIB->getsubgroup($p,$_GET['gid']);
	
	$sql.=" and ( userinfo.gbelong={$_GET['gid']} ";
	
	foreach($subgroup as $value)
	{
		$sql.=" or userinfo.gbelong={$value}";
	}
	$sql.=") ";

?>
<p><table border="1">
  <tr>
    <th width="8%">当前分组</th>
    <td>[<?php echo $d['gname'] ?>]</td>
  </tr>
<?php if ($d['parent']!=-1) {
	$sql2="select * from groups where gid={$d['parent']}";
	$q->dosql($sql2);
	$e=$q->rtnrlt(0);
?>
  <tr>
    <th>上级分组</th>
    <td>[<a href="userlist.php?gid=<?php echo $e['gid'] ?>"><?php echo $e['gname'] ?></a>]</td>
  </tr>
<?php } ?>
<?php 
if ($subgroup!=array()) {
?>
  <tr>
    <th>下级分组</th>
    <td><?php 
	foreach($subgroup as $value)
	{
		$sql2="select * from groups where gid={$value}";
		$q->dosql($sql2);
		$e=$q->rtnrlt(0);
?>
		[<a href="userlist.php?gid=<?php echo $e['gid'] ?>"><?php echo $e['gname'] ?></a>]
<?php 
	}
?></td>
  </tr>
<?php } ?>
  <tr>
    <th>备注</th>
    <td><?php echo nl2br(sp2n(htmlspecialchars($d['memo']))) ?></td>
  </tr>
</table></p>

<?php

	if ($_GET['key']!="")
		$sql.=" and (nickname like '%{$_GET[key]}%' or uid ='{$_GET[key]}' or usr like '%{$_GET[key]}%' or realname like '%{$_GET[key]}%')";
	
	if($_GET['rank']=="按通过数量排名")
		$sql.=" order by accepted desc";
	else if($_GET['rank']=="按通过率排名")
		$sql.=" order by accepted/submited desc";
	else if($_GET['rank']=="按等级排名")
		$sql.=" order by grade desc";
	else
		$sql.=" order by uid asc";
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

<form id="form1" name="form1" method="get" action="">
  搜索用户
  <input name="key" type="text" id="key" class="InputBox" value="<?php echo $_GET['key'] ?>" />
  <input name="sc" type="submit" id="sc" value="搜索"  class="LinkButton" />
  <input name="caid" type="hidden" id="caid" value="<?php echo $_GET['caid'] ?>" />
</form>
<form action="" method="get" name="rank">
  <input name="rank" type="submit" id="rank" class="LinkButton" value="按用户ID排名" />
  <input name="rank" type="submit" id="rank" class="LinkButton" value="按通过数量排名" />
  <input name="rank" type="submit" id="rank" class="LinkButton" value="按通过率排名" />
  <input name="rank" type="submit" id="rank" class="LinkButton" value="按等级排名" />
</form>
<? page_slice($cnt, $_GET['page'], '?key='.$_GET['key'].'&sc'.$_GET['sc'].'&rank='.$_GET['rank'].'&caid='.$_GET['caid'].'&'); ?>
<table width="100%" border="1" >
  <tr>
    <th scope="col">UID</th>
    <th scope="col">用户昵称</th>
    <th scope="col">名次</th>
    <th scope="col">通过</th>
    <th scope="col">通过率</th>
    <th scope="col">等级</th>
    <th scope="col">权限</th>
    <th scope="col">所属分组</th>
    <?php if ($_SESSION['admin']>0){ ?>
    <th scope="col" style=admin>真实姓名</th>
    <th scope="col" style=admin>IP</th>
    <th scope="col" style=admin>操作</th>
<script language=javascript>
function okdel(name) {
if(confirm("你确定要删除 "+name+" 的所有信息吗？\n删除之后将不可恢复！"))
return true;
return false;
}
</script>
<?php } ?>
  </tr>
<?php
	if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SETTINGS['style_pagesize'] ;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><?php echo $d['uid'] ?></td>
    <td>
<?=gravatar::showImage($d['email']);?>
<a href="../user/detail.php?uid=<?php echo $d['uid'] ?>" target="_blank"><?php echo $d['nickname'] ?></a></td>
    <td><?php echo $i+1; ?></td>
    <td><?php echo $d['accepted'] ?></td>
    <td><?php echo @round($d['accepted']/$d['submited']*100,2); ?>%</td>
    <td><?php echo $d['grade'] ?></td>
    <td><?php echo $STR[adminn][$d['admin']] ?></td>
    <td><?php echo "<a href='?gid={$d[gbelong]}'>{$d['gname']}</a>"; ?></td>
    <?php if ($_SESSION['admin']>0){ ?>
    <td style=admin><?php echo $d['realname'] ?></td>
    <td style=admin><?php echo $d['lastip'] ?></a></td>
    <td style=admin>
<a href="../admin/user/edituser.php?uid=<?php echo $d['uid'] ?>">修改</a>
<a href="../admin/user/doedituser.php?uid=<?php echo $d['uid'] ?>&action=del" onclick="return okdel('<?=$d['nickname']?>')">删除</a>
</td>
	<?php } ?>
  </tr>
<?php
	}
?>
</table>
</p>
<?php
	include_once("../include/stdtail.php");
?>
