<?php
require_once("../include/header.php");
$pid=(int)$_GET['pid'];
$uid=(int)$_GET['uid'];
$aid=(int)$_GET['aid'];
$ctid=(int)$_GET['ctid'];
if($uid)
    gethead(1,"","题目评论", $uid);
else 
    gethead(1,"","题目评论");
$LIB->hlighter();
$LIB->mathjax();

$p=new DataAccess();
$q=new DataAccess();
?>
<div class='row-fluid'>
<form method="get" action="" class='form-search'>
<? if($_SESSION['ID']) { ?>
<? if($pid) { ?>
<a class='btn btn-danger' href="comment.php?pid=<?=$pid?>">发表评论</a>
<? } else if($aid) { ?>
<a class='btn btn-danger' href="comment.php?aid=<?=$aid?>">发表评论</a>
<? } else if($ctid) { ?>
<a class='btn btn-danger' href="comment.php?ctid=<?=$ctid?>">发表评论</a>
<? } ?>
<? } ?>
<? if($pid) { ?>
<a class='btn' href="problem.php?pid=<?=$pid?>">返回原题</a>
<? } else if($aid) { ?>
<a class='btn' href="../page/page.php?aid=<?=$aid?>">返回原页</a>
<? } else if($ctid) { ?>
<a class='btn' href="../contest/problem.php?ctid=<?=$ctid?>">返回比赛</a>
<? } ?>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=$_GET['key']?>' placeholder='搜索评论'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
<? if(!($_GET['show'] || $pid || $aid || $uid)) { ?>
<a href="comments.php?show=yes" class='btn btn-inverse'>全部评论</a>
<? } ?>
</div>
<span>
题目PID
<input name="pid" type="number" value="<?=$_GET['pid']?>" class='span1' />
用户UID
<input name="uid" type="number" value="<?=$_GET['uid']?>" class='span1' />
<button type="submit" class='btn btn-primary'>检索</button>
</span>
</form>
<?php
/*$sql="select comments.*,userinfo.*,problem.pid,problem.probname from comments,userinfo,problem where userinfo.uid=comments.uid and problem.pid=comments.pid ";
if($pid) $sql.=" and problem.pid=$pid";
if ($_GET['key']!="")
$sql.=" and (problem.probname like '%{$_GET[key]}%' or problem.pid ='{$_GET[key]}' or problem.filename like '%{$_GET[key]}%' or comments.detail like '%{$_GET[key]}%' or userinfo.nickname like '%{$_GET[key]}%' or userinfo.usr like '%{$_GET[key]}%' or userinfo.realname like '%{$_GET[key]}%')";
if($pid) $sql.=" order by comments.cid asc"; else $sql.=" order by comments.stime desc";*/
$key=mysql_real_escape_string($_GET['key']);
$sql="SELECT comments.*, userinfo.uid, userinfo.nickname, userinfo.realname, userinfo.email, userinfo.accepted, userinfo.submited, userinfo.grade, userinfo.memo FROM comments, userinfo WHERE userinfo.uid = comments.uid ";
if($key) $sql.="AND (comments.detail LIKE '%$key%' OR userinfo.nickname LIKE '%$key%' OR userinfo.usr LIKE '%$key%' OR userinfo.realname LIKE '%$key%') ";
if($pid) {
    $sql.="AND $pid = comments.pid ";
    $sql.="ORDER BY comments.cid asc";
} else if($aid) {
    $sql.="AND $aid = comments.aid ";
    $sql.="ORDER BY comments.cid asc";
} else if($uid) {
    $sql.="AND $uid = comments.uid ";
    $sql.="ORDER BY comments.stime desc";
} else {
    $sql.="ORDER BY comments.stime desc";
}
$limitt=(int)$SET['style_pagesize'];
if(!($_GET['show'] || $pid || $aid || $uid))
    $sql .= " limit {$limitt}";
//echo "<pre>".$sql."</pre>";
$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?
if($cnt) {
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'];$i++) {
		$d=$p->rtnrlt($i);
		if ($d['uid']==$_SESSION['ID']) {
			$sended=1;
			$detail=$d['detail'];
			$sc=$d['showcode'];
		}
?>
<tr>
<td valign='top' style="width:64px;">
<a href="<?php echo 路径("user/detail.php?uid={$d['uid']}");?>">
<?=gravatar::showImage($d['email'], 64);?>
</a>
</td>
<td valign='top' style="width:120px;">
<div>
<a href="<?=路径("mail/index.php")?>?toid=<?=$d['uid']?>" title="给<?=$d['nickname']?>发送信件" class="pull-right"><span class="icon-envelope"></span></a>
<a href="?uid=<?=$d['uid']?>"><b><?php echo $d['nickname'];?></b></a>
</div>
积分：<?=$d['grade']?><br />
提交：<?=$d['accepted']?> / <?=$d['submited']?>
</td>
<td colspan=4 class="wrap">
<? if($_SESSION['ID']==$d['uid']) echo "<a href='comment.php?cid={$d['cid']}' class='pull-right btn btn-mini btn-warning'>修改</a>";?>
<?php echo BBCode($d['detail'])?>
<div class='muted pull-right'><small><?php echo BBCode($d['memo'])?></small></div>
</td>
</tr>
<tr class="<?=$d['pid']?"info":"success"?>">
<td colspan=2>
<?if(有此权限("查看用户")) echo $d['realname'];?>
<span class="pull-right">
<? if($d['pid']) { ?>
<a href="?pid=<?=$d['pid']?>">题目 <?=$d['pid']?></a>
<a href='problem.php?pid=<?=$d['pid']?>' target='_blank'><span class='icon-share'></span></a>
<? } else if($d['aid']) { ?>
<a href="?aid=<?=$d['aid']?>">页面 <?=$d['aid']?></a>
<a href='../page/page.php?aid=<?=$d['aid']?>' target='_blank'><span class='icon-share'></span></a>
<? } ?>
</span>
</td>
<td><?php if ($d['showcode']){
	$sql="select sid,result from submit where uid='{$d['uid']}' and pid='{$d['pid']}' order by subtime desc";
	$q->dosql($sql);
	$e=$q->rtnrlt(0);
?>
<a href="../submit/code.php?id=<?=$e['sid']?>" target="_blank" title="<?=$e['result']?>"><i class='icon icon-download'></i><?=评测结果($e['result'], 30, true)?></a>
<?php } ?>
</td>
<td><span class="pull-right"><?php echo date('Y-m-d H:i:s',$d['stime']);?></span></td>
<td style="width: 6em;">
<? if($_GET['show'] || $pid || $aid || $uid) { ?>
<span class="pull-right"><?=($i+1)?>楼</span>
<? } ?>
</td>
<td style="width: 4em;">
<a class='btn btn-mini btn-danger pull-right' href="comment.php?ccid=<?=$d['cid']?>&pid=<?=$d['pid']?>&aid=<?=$d['aid']?>&user=<?=$d['nickname']?>">回复</a>
</td>
</tr>
<?php
	}
} else {
	echo "<div class='alert'>还没有人发表评论！</div>";
}
?>
<? if(!($_GET['show'] || $pid || $aid || $uid)) { ?>
<tr class="danger">
<td colspan=6><center>
<a href="comments.php?show=yes">全部评论</a>
</center></td></tr>
<? } ?>
</table>
<?
if($cnt > $limitt && ($_GET['show'] || $pid || $aid || $uid))
    分页($cnt, $_GET['page'], '?show='.$_GET['show'].'?key='.$_GET['key'].'&');
?>
</div>
<?php
include_once("../include/footer.php");
?>
