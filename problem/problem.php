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
    if (!$d[submitable] && !有此权限('查看题目')) {
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
    if (!$promise && !有此权限('查看题目'))
        exit;
    $pid=$d[pid];
} else {
    echo '<script>document.location="../error.php?id=11"</script>';
}
?>

<div class='container-fluid'>
<div class='span4'>
<table id="probinfo" class='table table-condensed'>
<tr><th width='80px'>题目名称</th>
<td><?=$d[pid]; ?>. <b><?=$d['probname']; ?></b></td></tr>
<tr><th>难度等级</th>
<td><?=难度($d['difficulty']); ?></td></tr>
<tr><th>文件名称</th>
<td><code><?=$d['filename']?>.pas/c/cpp</code></td></tr>
<tr><th>输入输出</th>
<td><code><?=$d['filename']?>.in/out</code></td></tr>
<tr><th>时间限制</th>
<td><?=$d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?=$d['memorylimit']; ?> MB </td></tr>
<tr><th>对比方式</th>
<td><?=$STR['plugin'][$d['plugin']]; ?></td></tr>
<tr><th>测试点数</th>
<td><?=$d['datacnt']; ?> <?=$d['submitable']?"<span class=ok>可提交":"<span class=no>不可提交"?></span></td></tr>
<tr>
<th>添加时间</th>
<td>
<?php if(有此权限('修改题目')) { ?>
<a class=admin href="../admin/problem/editprob.php?action=edit&pid=<?= $d[pid]; ?>">[修改该题]</a>
<? } ?>
<?=date('Y-m-d', $d['addtime']) ?>
</td></tr>
<tr><th>开放分组</th>
<td><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank">[<?=$d['gname'] ?>]</a></td></tr>
<tr><th><a href="../information/submitlist.php?pid=<?=$pid; ?>">提交状态</a></th>
<td><?php
if($_SESSION['ID']) {
    $sql="SELECT * FROM submit WHERE pid ={$pid} AND uid ={$_SESSION['ID']} order by score desc limit 1";
    $ac=$q->dosql($sql);
    if ($ac) {
        $e=$q->rtnrlt(0);
        echo "<a href='code.php?id={$e['sid']}'>";
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
    echo " <a href='index.php?caid={$e[caid]}'>{$e[cname]}</a> ";
}
?></td></tr>
<? if($_SESSION['ID']) { ?>
<tr class='well'><form action="../compile/" method="post" enctype="multipart/form-data" class='form-inline'>
<td colspan=2>
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="102400" />
<input type="file" name="file" title='选择程序源文件' />
<?php if(有此权限('测试题目')) { ?>
<label class='checkbox pull-right'>
<input name="testmode" type="checkbox" value="1" />不写入数据库
</label>
<?php } ?>
<select name='judger' class='input-medium'>
<option value=0 selected=selected>自动选择评测机</option>
<?
$sql="select grid,address,memo from grader where enabled=1 order by priority desc";
$cnt=$q->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $e=$q->rtnrlt($i);
    echo "<option value={$e['grid']} >{$e['memo']}</option>";
}
?>       
</select>
<button type='submit' class='btn btn-primary' >提交代码</button>
<div class='btn-group pull-right' data-toggle='buttons-radio'>
<button type='button' class='btn' name="lang" value="pas">Pascal</button>
<button type='button' class='btn' name="lang" value="c">C</button>
<button type='button' class='btn active' name="lang" value="cpp">C++</button>
</div>
</td></form></tr>
<? } ?>
</table>
<table id="singlerank" class='table table-condensed'>
<tr><th colspan=4>综合排行前 <?=$SET['style_single_ranksize']; ?> 名</th><tr>
<?php $LIB->singlerank($q,$pid) ?>
</table>
<table id="Comments" class='table table-condensed'>
<tr><th colspan=3>最新讨论
<a href="comments.php?pid=<?=$pid?>"><b>[发表看法]</b></a>
</th></tr>
<?
$sql="select comments.*,userinfo.nickname,userinfo.uid,userinfo.email from comments,userinfo where userinfo.uid=comments.uid and comments.pid='{$d['pid']}' order by comments.stime desc";
$cnt=$q->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $e=$q->rtnrlt($i);
?>
    <tr class="CommentsU">
    <td><a href="../user/detail.php?uid=<?=$e['uid'] ?>"><?=gravatar::showImage($e['email']);?><?=$e['nickname'] ?></a></td>
    <td><?=date('Y-m-d H:i:s',$e['stime'])?></td>
    <td>
<? if($_SESSION['ID']) { ?>
    <a href="<?=路径("mail/index.php")?>?toid=<?=$e['uid']?>" title="给<?=$e['nickname']?>发送信件"><span class="icon-envelope"></span></a>
<? } ?>
    #<?=$cnt-$i?></td>
    </tr>
    <tr><td colspan=3 class="CommentsK"><?php echo nl2br(sp2n(htmlspecialchars($e['detail'])))?></td></tr>
<?
}
?>
</table>
</div>
<div id="probdetail" class='span8'>
<?=$d['detail']?>
</div>
</div>

<?php
include_once("../include/stdtail.php");
?>
