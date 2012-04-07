<?php
require_once("../include/stdhead.php");
gethead(1,"","题目");

$p=new DataAccess();
$q=new DataAccess();
$r=new DataAccess();

$sql="select problem.*,groups.* from problem,groups where pid=".(int)$_GET[pid]." and groups.gid=problem.group limit 1";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);

if($cnt) {
    if ($d[readforce]>$_SESSION[readforce]) {
        echo '<script>document.location="../error.php?id=17"</script>';
        exit;
    }
    if (!$d[submitable] && !($_SESSION['admin']>0)) {
        echo '<script>document.location="../error.php?id=18"</script>';
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
    $pid=$d[pid];
} else {
    echo '<script>document.location="../error.php?id=11"</script>';
}
?>

<table id="pdetail"><tr>
<td id="prob_left">
<table id="probinfo">
<tr><th width=80px>题目名称</th>
<td><b><?=$d[probname]; ?></b></td></tr>
<tr><th>难度等级</th>
<td><?=难度($d['difficulty']); ?></td></tr>
<tr><th>程序文件</th>
<td><?=$d[filename]; ?>.cpp/pas/c</td></tr>
<tr><th>输入输出</th>
<td><?=$d[filename]; ?>.in/out</td></tr>
<tr><th>时间限制</th>
<td><?=$d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?=$d['memorylimit']; ?> MB </td></tr>
<tr><th>对比方式</th>
<td><?=$STR['plugin'][$d['plugin']]; ?></td></tr>
<tr><th>测试点数</th>
<td><?=$d[datacnt]; ?></td></tr>
<tr><th>添加时间</th>
<td><?=date('Y-m-d', $d['addtime']) ?></td></tr>
<tr><th>开放分组</th>
<td><a href="../information/userlist.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td></tr>
<tr><th><a href="../information/submitlist.php?pid=<?=$pid; ?>">提交状态</a></th>
<td><?php
if($_SESSION['ID']) {
    $sql="SELECT * FROM submit WHERE pid ={$pid} AND uid ={$_SESSION['ID']} order by score desc limit 1";
    $ac=$q->dosql($sql);
    if ($ac) {
        $e=$q->rtnrlt(0);
        echo "<a href='submitdetail.php?id={$e['sid']}'>";
        评测结果($e['result']);
        echo "</a>";
    }
} ?></td></tr>
<tr><th>所属分类</th>
<td><?php
$sql="select category.cname,category.caid from category,tag where tag.pid={$_GET[pid]} and category.caid=tag.caid";
$cnt2=$r->dosql($sql);
for ($i=0;$i<=$cnt2-1;$i++) {
    $e=$r->rtnrlt($i);
    echo " <a href='problist.php?caid={$e[caid]}'>{$e[cname]}</a> ";
}
?></td></tr>
<tr><?php if ($_SESSION['admin']>0){ ?>
<th class=admin><a href="../admin/problem/editprob.php?action=edit&pid=<?= $d[pid]; ?>">修改该题</a></th>
<?php } else { ?>
<td></td>
<? } ?>
<td align=right>
<a href="comments.php?pid=<?=$pid?>"><b>发表看法</b></a>
</td></tr>
<tr><form action="../compile/" method="post" enctype="multipart/form-data" name="sub">
<td colspan=2 align=right>
<input type="file" name="file"/>
<input type="radio" name="lang" id="pas" value="pas" title="Pascal" /><label for="pas">Pascal</label>
<input type="radio" name="lang" id="c" value="c"  title="C" /><label for="c">C</label>
<input type="radio" name="lang" id="cpp" value="cpp" checked=1 title="C++" /><label for="cpp">C++</label>
<?php if ($_SESSION['admin']>0){ ?>
<input name="testmode" type="checkbox" id="testmode" value="1" /> 
<label for="testmode">测试模式</label>
<?php } ?>
<input type="submit" name="Submit" value="提交代码"/>
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']; ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="102400">
</td></form></tr>
</table>
<table id="singlerank">
<tr><th colspan=3>运行速度前 <?=$SET['style_single_ranksize']; ?> 名</th><tr>
<tr><th colspan=3>C++</th></tr>
<?php $LIB->singlerank($p,$pid,2) ?>
<tr><th colspan=3>Pascal</th></tr>
<?php $LIB->singlerank($p,$pid,0) ?>
<tr><th colspan=3>C</th></tr>
<?php $LIB->singlerank($p,$pid,1) ?>
</table>
</td>
<td>
<div id="probdetail">
<?=$d['detail'] ?>
</div>
</td>
</tr></table>

<?php
include_once("../include/stdtail.php");
?>
