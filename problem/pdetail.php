<?php
require_once("../include/stdhead.php");

$pid = (int)$_GET['pid'];
$db = @mysql_connect($cfg['data_server'],$cfg['data_uid'],$cfg['data_pwd']);
@mysql_select_db($cfg['data_database'],$db);
@mysql_query("set names utf8");
$res = @mysql_query("select probname from problem where pid=$pid");
$ress = @mysql_fetch_object($res);
$title = $ress->probname;
@mysql_close($db);
gethead(1,"",$pid.": ".$title);

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
<tr><th width=80px>题目编号</th>
<td><?=$d[pid]; ?></td></tr>
<tr><th>题目名称</th>
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
<td><?=$d[datacnt]; ?> <?=$d['submitable']?"<span class=ok>可提交":"<span class=no>不可提交"?></span></td></tr>
<tr>
<th>添加时间</th>
<td>
<?php if ($_SESSION['admin']>0){ ?>
<a class=admin href="../admin/problem/editprob.php?action=edit&pid=<?= $d[pid]; ?>">[修改该题]</a>
<? } ?>
<?=date('Y-m-d', $d['addtime']) ?>
</td></tr>
<tr><th>开放分组</th>
<td><a href="../information/userlist.php?gid=<?=$d['gid'] ?>" target="_blank">[<?=$d['gname'] ?>]</a></td></tr>
<tr><th><a href="../information/submitlist.php?pid=<?=$pid; ?>">[提交状态]</a></th>
<td><?php
if($_SESSION['ID']) {
    $sql="SELECT * FROM submit WHERE pid ={$pid} AND uid ={$_SESSION['ID']} order by score desc limit 1";
    $ac=$q->dosql($sql);
    if ($ac) {
        $e=$q->rtnrlt(0);
        echo "<a href='submitdetail.php?id={$e['sid']}'>";
        echo $STR['lang'][$e['lang']];
        echo "</a> ";
        评测结果($e['result']);
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
<tr><th colspan=4>综合排行前 <?=$SET['style_single_ranksize']; ?> 名</th><tr>
<?php $LIB->singlerank($q,$pid) ?>
</table>
<hr />
<table id="Comments">
<tr><th colspan=3>最新讨论
<a href="comments.php?pid=<?=$pid?>"><b>[发表看法]</b></a>
</th><tr>
<?
$sql="select comments.*,userinfo.nickname,userinfo.uid,userinfo.email from comments,userinfo where userinfo.uid=comments.uid and comments.pid='{$d['pid']}' order by comments.stime desc limit {$SET['style_single_ranksize']}";
$cnt=$q->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $e=$q->rtnrlt($i);
?>
    <tr class="CommentsU">
    <td width=10px><a href="<?=路径("mail/index.php")?>?toid=<?=$e['uid']?>" title="给<?=$e['nickname']?>发送信件"><span class="icon-envelope"></span></a></td>
    <td><a href="../user/detail.php?uid=<?=$e['uid'] ?>"><?=gravatar::showImage($e['email']);?><?=$e['nickname'] ?></a></td>
    <td width=30px>#<?=$cnt-$i?></td>
    </tr>
    <tr><td colspan=3 class="CommentsK"><?php echo nl2br(sp2n(htmlspecialchars($e['detail'])))?></td></tr>
    <tr><td colspan=3 class="CommentsTime"><?=date('Y-m-d H:i:s',$e['stime'])?></td></tr>
<?
}
?>
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
