<?php
require_once("../include/stdhead.php");
gethead(1,"","页面");

$p=new DataAccess();
$q=new DataAccess();
$r=new DataAccess();

$sql="select page.*,groups.*,userinfo.nickname from page,groups,userinfo where aid=".(int)$_GET[aid]." and userinfo.uid=page.uid and groups.gid=page.group limit 1";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);

if($cnt) {
    if ($d[force]>$_SESSION[readforce]) {
        echo '<script>document.location="../error.php?id=17"</script>';
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
    if (!$promise && !有此权限($q, '查看页面'))
        exit;
    $aid=$d[aid];
} else {
    echo '<script>document.location="../error.php?id=11"</script>';
}
?>

<table>
<tr><th>页面名称</th>
<th><b><?=$d[title]?></b></th>
<?php if(有此权限($q, '修改页面')) { ?>
<td><a class=admin href="editpage.php?action=edit&aid=<?= $d[aid]; ?>">修改该页</a></td>
<?php } else { ?>
<td></td>
<? } ?>
<td><?=$d['nickname']?></td>
</tr>
<tr><th>添加时间</th>
<td><?=date('Y-m-d', $d['time']) ?></td>
<th>开放分组</th>
<td><a href="../information/userlist.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td></tr>
<tr><th>编辑时间</th>
<td><?=date('Y-m-d', $d['etime']) ?></td>
<th>阅读权限</th>
<td><?=$d['force'] ?></td>
</tr>
</td>
<tr>
<td id="probdetail" colspan=4>
<?=$d['text'] ?>
</td>
</tr>
</table>

<?php
include_once("../include/stdtail.php");
?>

