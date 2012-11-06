<?php
require_once("../include/header.php");
$p=new DataAccess();
$aid = (int)$_GET['aid'];
$sql="select page.*,groups.*,userinfo.nickname from page,groups,userinfo where aid=".(int)$_GET[aid]." and userinfo.uid=page.uid and groups.gid=page.group limit 1";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);
$title = $d['title'];
gethead(1,"",$title);

$LIB->hlighter();
$LIB->mathjax();

$q=new DataAccess();
$r=new DataAccess();

if($cnt) {
    if ($d[force]>$_SESSION[readforce]) {
        异常("你没有该页面阅读权限！",取路径("page/index.php"));
        exit;
    }
    $subgroup=$LIB->getsubgroup($q,$d['gid']);
    $subgroup[0]=$d['gid'];
    $promise=false;
    foreach($subgroup as $value) {
        if ($value==(int)$_SESSION['group']) {
            $promise=true;
            break;
        }
    }
    if (!$promise && !有此权限('查看页面'))
        exit;
    $aid=$d[aid];
} else {
    异常("页面不存在！",取路径("page/index.php"));
}
?>

<div class='row-fluid'>
<div class='page'>
<center class="problem tou">
<h1><?=$d['title']?>
<?php if(有此权限('修改页面')) { ?>
<a href="editpage.php?action=edit&aid=<?=$d['aid']?>" title="修改页面 <?=$d['title']?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</h1>
由 <a href="../user/detail.php?uid=<?=$d['uid']; ?>" target="_blank"><?=$d['nickname']?></a> 在 <?=date('Y-m-d', $d['time']) ?> 创建
开放分组：<a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a>
上次编辑时间：<?=date('Y-m-d', $d['etime'])?>
<?php if(有此权限('修改页面')) { ?>
<a href="editpage.php?action=edit&aid=<?=$d[aid]?>" class="btn btn-info btn-mini">编辑</a>
<? } ?>
<hr />
</center>
<dl class='problem'>
<?=$d['text'] ?>
</dl>
</div>
<div class="tou">
<? if($_SESSION['ID']) { ?>
<a href="../problem/comment.php?aid=<?=$aid?>" class="pull-right btn btn-danger">发表评论</a>
<? } ?>
<h4><a href="../problem/comments.php?aid=<?=$aid?>">关于 <b><?=shortname($d['title']); ?></b> 的讨论</a></h4>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?
$sql="SELECT comments.*, userinfo.uid, userinfo.nickname, userinfo.realname, userinfo.email, userinfo.accepted, userinfo.submited, userinfo.grade, userinfo.memo FROM comments, userinfo WHERE userinfo.uid = comments.uid ";
$sql.="AND $aid = comments.aid ";
$sql.="ORDER BY comments.cid asc";
$cnt=$q->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $d=$q->rtnrlt($i);
?>
<tr>
<td valign='top' style="width:64px;">
<a href="<?php echo 路径("user/detail.php?uid={$d['uid']}");?>">
<?=gravatar::showImage($d['email'], 64);?>
</a>
</td>
<td valign='top' style="width:120px;">
<div>
<a href="<?php echo 路径("user/detail.php?uid={$d['uid']}");?>"><b><?php echo $d['nickname'];?></b></a>
</div>
积分：<?=$d['grade']?><br />
提交：<?=$d['accepted']?> / <?=$d['submited']?>
</td>
<td colspan=3 class="wrap">
<? if($_SESSION['ID']==$d['uid']) echo "<a href='../problem/comment.php?cid={$d['cid']}' class='pull-right btn btn-mini btn-warning'><i class='icon icon-edit icon-white'></i></a>";?>
<?php echo BBCode($d['detail'])?>
<div class='muted pull-right'><small><?php echo BBCode($d['memo'])?></small></div>
</td>
</tr>
<tr class="success">
<td colspan=2>
<?if(有此权限("查看用户")) echo $d['realname'];?>
</td>
<td><span class="pull-right">发表时间：<?php echo date('Y-m-d H:i:s',$d['stime']);?></span></td>
<td style="width: 8em;"><span class="pull-right">帖子编号：<?=$d['cid']?></span></td>
<td style="width: 4em;"><span class="pull-right">#<?=($i+1)?></span></td>
</tr>
<?
}
?>
</table>
</div>
</div>

<?php
include_once("../include/footer.php");
?>

