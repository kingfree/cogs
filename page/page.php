<?php
require_once("../include/header.php");
gethead(1,"","页面");

$p=new DataAccess();
$q=new DataAccess();
$r=new DataAccess();

$sql="select page.*,groups.*,userinfo.nickname from page,groups,userinfo where aid=".(int)$_GET[aid]." and userinfo.uid=page.uid and groups.gid=page.group limit 1";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);

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

<div class='container-fluid'>
<div class='page-header'>
<h1><?=$d['title']?>
<?php if(有此权限('修改页面')) { ?>
<a href="editpage.php?action=edit&aid=<?=$d[aid]?>" class="btn btn-info">编辑</a>
<? } ?>
</h1>
由 <a href="user/detail.php?uid=<?=$d['uid']; ?>" target="_blank"><?=$d['nickname']?></a> 在 <?=date('Y-m-d', $d['time']) ?> 创建
开放分组：<a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a>
上次编辑时间：<?=date('Y-m-d', $d['etime'])?>
</div>
<div class='page'>
<?=$d['text'] ?>
</div>
</div>

<?php
include_once("../include/footer.php");
?>

