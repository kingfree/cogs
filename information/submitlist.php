<?php
require_once("../include/stdhead.php");
gethead(1,"","提交记录");

$p=new DataAccess();
$q=new DataAccess();
?>
<form id="search_submit" action="" method="get" >
检索： 用户UID
<input name="uid" type="text" id="uid" value="<?php echo $_GET['uid'] ?>" />
题目PID
<input name="pid" type="text" id="pid" value="<?php echo $_GET['pid'] ?>" />
<input name="sc" type="submit" id="sc" value="检索" />
</form>
<div id="sublist_now">
这里是 <b><?php
if ((int)$_GET['uid']==0) {
?>所有人<?
} else {
$sql="select nickname from userinfo where uid='{$_GET['uid']}'";
$cnt=$p->dosql($sql);
if (!$cnt) exit;
$d=$p->rtnrlt(0);
?><a href="../user/detail.php?uid=<?php echo $_GET['uid'] ?>" target="_blank"><?php echo $d['nickname'] ?></a><?php } ?></b>的<b><?php
if ((int)$_GET['pid']==0) {
?>所有题目<?
} else {
$sql="select probname from problem where pid='{$_GET['pid']}' limit 1";
$cnt=$p->dosql($sql);
if (!$cnt) exit;
$d=$p->rtnrlt(0);
?><a href="../problem/pdetail.php?pid=<?php echo $_GET['pid'] ?>" target="_blank"><?php echo $d['probname'] ?></a><?php } ?></b>的记录。
<a href="?pid=<?php echo $_GET['pid'] ?>&uid=<?php echo $_GET['uid'] ?>&display=all">全部显示</a>
<a href="?pid=<?php echo $_GET['pid'] ?>&uid=<?php echo $_GET['uid'] ?>&display=ac">只显示通过的</a>
</div>
<?php
$sql="select submit.*,userinfo.nickname,userinfo.email,userinfo.realname,problem.probname from submit,userinfo,problem where submit.pid=problem.pid and submit.uid=userinfo.uid ";
if ($_GET['display']=='ac')
    $sql.=" and submit.accepted=1 ";
if ($_GET['uid'])
    $sql.=" and submit.uid={$_GET['uid']} ";
if ($_GET['pid'])
    $sql.=" and submit.pid={$_GET['pid']} ";
$sql.=" order by submit.sid desc";

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
?>
<? 分页($cnt, $_GET['page'], '?pid='.$_GET['pid'].'&uid='.$_GET['uid'].'&display='.$_GET['display'].'&'); ?>

<table id="submitlist">
<tr>
<th>SID</th>
<th>题目</th>
<th>用户</th>
<th>结果</th>
<th>得分</th>
<th>时间</th>
<th>用时</th>
<th>内存</th>
<?php if ($_SESSION['admin']>0){ ?>
<th class=admin>IP</th>
<th class=admin>姓名</th>
<?php } ?>
</tr>
<?php if (!$err)
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=$p->rtnrlt($i);
?>
<tr>
<td align=center><?=$d['sid']?></td>
<td><?php 是否通过($d['pid'], $q);
echo "<a href='?pid={$d['pid']}'>{$d['probname']}</a>";
echo "<a href='../problem/pdetail.php?pid={$d['pid']}' target='_blank'><span class='icon-share'></span></a>";
?></td>
<td>
<a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'>
<?=gravatar::showImage($d['email']);?></a>
<?php echo "<a href='?uid={$d[uid]}'>{$d['nickname']}</a>"; ?></td>
<td><?php echo "<a href='../problem/submitdetail.php?id={$d['sid']}'>" ?><?=评测结果($d['result'])?></a></td>
<td align=center><span class="<?=$d['accepted']?'ok':'no'?>"><?php echo $d['score'] ?></span></td>
<td align=center><?php echo date('Y-m-d H:i:s',$d['subtime']); ?></td>
<td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
<td><?php printf("%.2f",$d['memory']/1024) ?> MiB </td>
<?php if ($_SESSION['admin']>0){ ?>
<td class=admin><?php echo $d['IP'] ?></td>
<td class=admin><?php echo $d['realname'] ?></td>
<?php } ?>
</tr>
<?php
}
?>
</table>

<?php
include_once("../include/stdtail.php");
?>
