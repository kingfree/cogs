<?php
require_once("../include/header.php");
$aid = (int)$_GET['aid'];
$db = @mysql_connect($cfg['data_server'],$cfg['data_uid'],$cfg['data_pwd']);
@mysql_select_db($cfg['data_database'],$db);
@mysql_query("set names utf8");
$res = @mysql_query("select title from page where aid=$aid");
$ress = @mysql_fetch_object($res);
$title = $ress->title;
@mysql_close($db);
gethead(1,"",$title);
$LIB->mathjax();

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

<div class='row-fluid'>
<div class='page'>
<center>
<h1><?=$d['title']?></h1>
由 <a href="../user/detail.php?uid=<?=$d['uid']; ?>" target="_blank"><?=$d['nickname']?></a> 在 <?=date('Y-m-d', $d['time']) ?> 创建
开放分组：<a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a>
上次编辑时间：<?=date('Y-m-d', $d['etime'])?>
<?php if(有此权限('修改页面')) { ?>
<a href="editpage.php?action=edit&aid=<?=$d[aid]?>" class="btn btn-info btn-mini">编辑</a>
<? } ?>
<hr />
</center>
<?=$d['text'] ?>
</div>
</div>

<?php
include_once("../include/footer.php");
?>

