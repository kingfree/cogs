<?php
require_once("../../include/stdhead.php");
gethead(1,"查看用户","用户登录日志");
$p=new DataAccess();
?>

<form id="search_submit" action="" method="get" >
检索： 用户UID
<input name="uid" type="text" id="uid" value="<?=$_GET['uid'] ?>" />
<input name="sc" type="submit" id="sc" value="检索" />
</form>
<?php
$sql="select login.*,userinfo.email,userinfo.realname from login,userinfo where login.uid=userinfo.uid";
if($_GET['uid']) $sql .= " and login.uid={$_GET['uid']}";
$sql .= " order by ltime desc";
$cnt=$p->dosql($sql);
$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
if(!$_GET['page']) {
    $_GET['page']=1;
    $st=0;
} else {
    if ($_GET[page]<1 || $_GET[page]>$totalpage)
        异常("页面错误！");
    else
        $st=(($_GET[page]-1)*$SET['style_pagesize']);
}
//if($_GET['show'])
分页($cnt, $_GET['page'], '?uid='.$_GET['uid']."&");
?>
<table id="loginlog">
<thead><tr>
<th width=40px>编号</th>
<th width=60px>用户</th>
<th width=120px>IP</th>
<th>UA</th>
<th width=180px>时间</th>
</tr></thead>
<?
if ($cnt) 
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
	$d=$p->rtnrlt($i);
?>
<tr>
<td><?=$d['lid']?></td>
<td><a href="../../user/detail.php?uid=<?=$d['uid']?>" target=_blank><?=gravatar::showImage($d['email']);?><?=$d['realname']?></a></td>
<td><?=$d['ip']?></td>
<td><?=$d['ua']?></td>
<td><?=$d['ltime']?></td>
</tr>
<? } ?>
</table>
<?php
	include_once("../../include/stdtail.php");
?>
