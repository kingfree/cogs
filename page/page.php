<?php
require_once("../include/stdhead.php");
gethead(1,"","页面");

$p=new DataAccess();
$q=new DataAccess();
$r=new DataAccess();

$sql="select page.*,groups.* from page,groups where aid=".(int)$_GET[aid]." and groups.gid=page.group limit 1";
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
    if (!$promise && !($_SESSION['admin']>0))
        exit;
    $aid=$d[aid];
} else {
    echo '<script>document.location="../error.php?id=11"</script>';
}
?>

<table>
<tr><th>页面名称</th>
<th><b><?=$d[title]?></b></th>
<?php if ($_SESSION['admin']>0){ ?>
<td><a class=admin href="editpage.php?action=edit&aid=<?= $d[aid]; ?>">修改该页</a></td>
<?php } else { ?>
<td></td>
<? } ?>
<td></td>
</tr>
<tr><th>添加时间</th>
<td><?=date('Y-m-d m:i:s', $d['time']) ?></td>
<th>编辑时间</th>
<td><?=date('Y-m-d m:i:s', $d['etime']) ?></td></tr>
<tr><th>开放分组</th>
<td><a href="../information/userlist.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td>
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

