<?php
require_once("../include/stdhead.php");
gethead(1,"","用户比较");

$user1 = $_GET['uid1'];
$user2 = $_GET['uid2'];

$p=new DataAccess();
?>

<table id="compare" width=100%><tr>
<td width=50%>
<table class="user_cmp_info">
<?
$sql="select * from userinfo where uid={$user1}";
$cnt=$p->dosql($sql);
if ($cnt) {
	$d=$p->rtnrlt(0);
    $username1 = $d['nickname'];
    $useremail1 = $d['email'];
?>
    <tr><th width=80px>用户编号</th>
    <td><?=$d['uid']?></td>
    <td rowspan=9><?=gravatar::showImage($d['email'], 200);?></td>
    </tr>
    <tr><th>用户名称</th>
    <td><?=$d['usr']?></td></tr>
    <tr><th>用户昵称</th>
    <td><?=$d['nickname']?></td></tr>
    <tr><th>通过提交</th>
    <td><?=$d['accepted']?> / <?=$d['submited']?></td></tr>
    <tr><th>用户等级</th>
    <td><?=$d['grade']?></td></tr>
    <tr><th>电子邮件</th>
    <td><?=$d['email']?></td></tr>
    <tr><th>管理权限</th>
    <td><?=$STR[adminn][$d['admin']]?></td></tr>
    <tr><th>注册时间</th>
    <td><?=date('Y-m-d H:i:s',$d['regtime'])?></td></tr>
    <tr><th>个人介绍</th>
    <td><?=nl2br(sp2n(htmlspecialchars($d['memo']))) ?></td></tr>
<? } ?>
</table>
<table class="user_cmp_prob">
<?
$sql="select problem.pid,problem.probname from problem,submit where submit.uid={$user1}";
$cnt=$p->dosql($sql);
if ($cnt) {
	$d=$p->rtnrlt(0);
?>
<? } ?>
</table>
<table class="user_cmp_cont">
</table>
</td>
<td width=50%>
<table class="user_cmp_info">
<?
$sql="select * from userinfo where uid={$user2}";
$cnt=$p->dosql($sql);
if ($cnt) {
	$d=$p->rtnrlt(0);
    $username2 = $d['nickname'];
    $useremail2 = $d['email'];
?>
    <tr><th width=80px>用户编号</th>
    <td><?=$d['uid']?></td>
    <td rowspan=9><?=gravatar::showImage($d['email'], 200);?></td>
    </tr>
    <tr><th>用户名称</th>
    <td><?=$d['usr']?></td></tr>
    <tr><th>用户昵称</th>
    <td><?=$d['nickname']?></td></tr>
    <tr><th>通过提交</th>
    <td><?=$d['accepted']?> / <?=$d['submited']?></td></tr>
    <tr><th>用户等级</th>
    <td><?=$d['grade']?></td></tr>
    <tr><th>电子邮件</th>
    <td><?=$d['email']?></td></tr>
    <tr><th>管理权限</th>
    <td><?=$STR[adminn][$d['admin']]?></td></tr>
    <tr><th>注册时间</th>
    <td><?=date('Y-m-d H:i:s',$d['regtime'])?></td></tr>
    <tr><th>个人介绍</th>
    <td><?=nl2br(sp2n(htmlspecialchars($d['memo']))) ?></td></tr>
<? } ?>
</table>
<table class="user_cmp_prob">
<?
$sql="select problem.pid,problem.probname from problem,submit where submit.uid={$user2}";
$cnt=$p->dosql($sql);
if ($cnt) {
	$d=$p->rtnrlt(0);
?>
<? } ?>
</table>
<table class="user_cmp_cont">
</table>
</td></table>

<?php
	include_once("../include/stdtail.php");
?>

