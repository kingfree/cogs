<?php
require_once("../include/header.php");
gethead(1,"查看用户","登录日志");
$p=new DataAccess();
?>

<form action="" method="get" class='form-inline center'>
检索： 用户UID
<input name="uid" type="text" id="uid" value="<?=$_GET['uid'] ?>" />
<button type="submit" class='btn'>检索</button>
</form>
<?php
$sql="select login.*,userinfo.email,userinfo.realname from login,userinfo where login.uid=userinfo.uid";
if($_GET['uid']) $sql .= " and login.uid={$_GET['uid']}";
$sql .= " order by ltime desc";
$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
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
<td><a href="detail.php?uid=<?=$d['uid']?>" target=_blank><?=gravatar::showImage($d['email']);?><?=$d['realname']?></a></td>
<td><?=$d['ip']?></td>
<td><?=$d['ua']?></td>
<td><?=$d['version']?></td>
<td><?=$d['ltime']?></td>
</tr>
<? } ?>
</table>
<?php
//if($_GET['show'])
分页($cnt, $_GET['page'], '?uid='.$_GET['uid']."&");
include_once("../include/footer.php");
?>
