<?php
require_once("../include/stdhead.php");
gethead(1,"","提交记录");

$p=new DataAccess();
$q=new DataAccess();
?>
<p><form action="" method="get" >
检索 用户UID
<input name="uid" type="text" id="uid" class="InputBox" value="<?php echo $_GET['uid'] ?>" />
题目PID
<input name="pid" type="text" id="pid" class="InputBox" value="<?php echo $_GET['pid'] ?>" />
<input class="LinkButton" name="sc" type="submit" id="sc" value="检索" class="Button" />
</form></p>
<p>您现在正在查看<strong><?php
if ((int)$_GET['uid']==0) {
?>所有人<?
} else {
$sql="select nickname from userinfo where uid='{$_GET['uid']}'";
$cnt=$p->dosql($sql);
if (!$cnt) exit;
$d=$p->rtnrlt(0);
?><a href="../user/detail.php?uid=<?php echo $_GET['uid'] ?>" target="_blank"><?php echo $d['nickname'] ?></a><?php } ?></strong>的<strong><?php
if ((int)$_GET['pid']==0) {
?>所有题目<?
} else {
$sql="select probname from problem where pid='{$_GET['pid']}'";
$cnt=$p->dosql($sql);
if (!$cnt) exit;
$d=$p->rtnrlt(0);
?><a href="../problem/pdetail.php?pid=<?php echo $_GET['pid'] ?>" target="_blank"><?php echo $d['probname'] ?></a><?php } ?></strong>的记录
<a class="LinkButton" href="?pid=<?php echo $_GET['pid'] ?>&uid=<?php echo $_GET['uid'] ?>&display=all">全部显示</a>
<a class="LinkButton" href="?pid=<?php echo $_GET['pid'] ?>&uid=<?php echo $_GET['uid'] ?>&display=ac">只显示通过的</a>
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
$totalpage=(int)(($cnt-1)/$SETTINGS['style_pagesize'])+1;
if (!isset($_GET[page])) {
    $_GET[page]=1;
    $st=0;
} else {
    if ($_GET[page]<1 || $_GET[page]>$totalpage) {
        echo "页错误！";
        $err=1;
    } else
        $st=(($_GET[page]-1)*$SETTINGS['style_pagesize']);
}?>
<? page_slice($cnt, $_GET['page'], '?pid='.$_GET['pid'].'&uid='.$_GET['uid'].'&display='.$_GET['display'].'&'); ?>

<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
<tr>
<th scope="col">SID</th>
<th scope="col">题目名称</th>
<th scope="col">用户</th>
<th scope="col">评测结果</th>
<th scope="col">得分</th>
<th scope="col">提交时间</th>
<th scope="col">时间</th>
<th scope="col">内存</th>
<?php if ($_SESSION['admin']>0){ ?>
<th bgcolor="#99FFCC" scope="col">IP</th>
<th bgcolor="#99FFCC" scope="col">姓名</th>
<?php } ?>
</tr>
<?php if (!$err)
for ($i=$st;$i<$cnt && $i<$st+$SETTINGS['style_pagesize'] ;$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td><?php echo $d['sid'] ?></td>
<td><?php
echo "<a href='../problem/pdetail.php?pid=".$d['pid']."' target='_blank'>";
if ($_SESSION['ID']) {
$sql="SELECT * FROM submit WHERE pid={$d['pid']} AND uid={$_SESSION['ID']} order by accepted desc limit 1";
$ac=$q->dosql($sql);
if ($ac) {
$e=$q->rtnrlt(0);
if ($e['accepted'])
echo "<img src='../images/sign/right.gif' border='0' />";
else echo "<img src='../images/sign/error.gif' border='0' />";
} else echo "<img src='../images/sign/todo.gif' border='0' />";
}
echo "</a>";
echo "<a href='?pid={$d['pid']}'>{$d['probname']}</a>";
?></td>
<td>
<a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'>
<?=gravatar::showImage($d['email'], 16);?></a>
<?php echo "<a href='?uid={$d[uid]}'>{$d['nickname']}</a>"; ?></td>
<td><?php echo "<a href='../problem/submitdetail.php?id={$d['sid']}'>" ?><pre class="no-highlight" style='margin:0;'><?=judgeresult($d['result'])?></pre></a></td>
<td><?php echo $d['score'] ?></td>
<td><?php echo date('Y-m-d H:i:s',$d['subtime']); ?></td>
<td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
<td><?php printf("%.2f",$d['memory']/1024) ?> MiB </td>
<?php if ($_SESSION['admin']>0){ ?>
<td bgcolor="#99FFCC"><a href="../addons/ipquery/?ip=<?php echo $d['IP'] ?>" target="_blank"><?php echo $d['IP'] ?></a></td>
<td bgcolor="#99FFCC"><?php echo $d['realname'] ?></td>
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
