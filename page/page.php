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
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr><th colspan=3>
<a href="../problem/comments.php?aid=<?=$aid?>">关于 <b><?=shortname($d['title']); ?></b> 的讨论</a>
<? if($_SESSION['ID']) { ?>
<a href="../problem/comment.php?aid=<?=$aid?>" class="pull-right btn btn-mini btn-danger"><b>发表评论</b></a>
<? } ?>
</th></tr>
<?
$sql="SELECT comments.*, userinfo.uid, userinfo.nickname, userinfo.realname, userinfo.email, userinfo.accepted, userinfo.submited, userinfo.grade, userinfo.memo FROM comments, userinfo WHERE userinfo.uid = comments.uid ";
$sql.="AND $aid = comments.aid ";
$sql.="ORDER BY comments.cid desc";
$cnt=$q->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $e=$q->rtnrlt($i);
?>
    <tr class="CommentsU">
    <td><a href="../user/detail.php?uid=<?=$e['uid'] ?>"><?=gravatar::showImage($e['email']);?><?=$e['nickname'] ?></a></td>
    <td><?=date('y/m/d H:i',$e['stime'])?>
   </td>
    <td>
    #<?=$cnt-$i?></td>
    </tr>
    <tr><td colspan=3 class="CommentsK wrap">
    <? if($_SESSION['ID']==$e['uid']) echo "<a href='../problem/comment.php?cid={$e['cid']}' class='pull-right btn btn-mini btn-warning'><i class='icon icon-edit icon-white'></i></a>";?>
    <?php echo BBCode($e['detail'])?>
    </td></tr>
<?
}
?>
</table>
</div>

<?php
include_once("../include/footer.php");
?>

