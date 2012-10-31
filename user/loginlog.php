<?php
require_once("../include/header.php");
gethead(1,"sess","登录日志",$_GET['uid']);
$p=new DataAccess();
if($_GET['uid'] != $_SESSION['ID'] && !有此权限("查看用户"))
异常("不是本人并且没有权限查看！", 取路径("user/detail.php?uid={$_GET['uid']}"));
?>

<form action="" method="get" class='form-inline center'>
检索： 用户UID
<input name="uid" type="number" value="<?=$_GET['uid']?>" class='span1' />
<button type="submit" class='btn btn-primary'>检索</button>
</form>
<?php
$sql="select login.*,userinfo.email,userinfo.realname from login,userinfo where login.uid=userinfo.uid";
if($_GET['uid']) $sql .= " and login.uid={$_GET['uid']}";
$sql .= " order by ltime desc";
$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th width=40px>编号</th>
<th width=60px>用户</th>
<th width=120px>IP</th>
<th>UA</th>
<th width=80px>版本</th>
<th width=180px>时间</th>
</tr></thead>
<?
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
	$d=$p->rtnrlt($i);
?>
<tr>
<td><?=$d['lid']?></td>
<td><a href="?uid=<?=$d['uid']?>"><?=gravatar::showImage($d['email']);?><?=$d['realname']?></a></td>
<td><?=$d['ip']?></td>
<td><?=$d['ua']?></td>
<td><?=$d['version']?></td>
<td><?=$d['ltime']?></td>
</tr>
<? } ?>
</table>
</div>
<?php
//if($_GET['show'])
分页($cnt, $_GET['page'], '?uid='.$_GET['uid']."&");
include_once("../include/footer.php");
?>
