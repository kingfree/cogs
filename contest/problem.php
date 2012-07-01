<?php
require_once("../include/header.php");
gethead(1,"","比赛题目");
$uid = (int) ($_GET['uid'] ? $_GET['uid'] : $_SESSION['ID']);
$p=new DataAccess();
$r=new DataAccess();
$sql="select * from problem where pid={$_GET[pid]}";
$cnt=$p->dosql($sql);
if ($cnt) {
    $d=$p->rtnrlt(0);
    $q=new DataAccess();
    $sql="select cname,starttime,endtime,contains,intro,groups.* from compbase,comptime,groups where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]} and comptime.group=groups.gid";
    $c=$q->dosql($sql);
    $e=$q->rtnrlt(0);
    
    $subgroup=$LIB->getsubgroup($q,$e['gid']);
    $subgroup[0]=$e['gid'];
    $promise=false;
    foreach($subgroup as $value) {
        if ($value==(int)$_SESSION['group']) {
            $promise=true;
            break;
        }
    }
    if (!$promise && !有此权限('查看比赛'))
        异常("你没有权限访问该页面！",取路径("contest/index.php"));
    if (!$c)
        异常("比赛场次不存在！",取路径("contest/index.php"));
    $pbs=explode(":",$e['contains']);
    $pb=0;
    foreach($pbs as $k=>$v) {
        if ($v==$_GET['pid'])
            $pb=1;
    }
    if (!有此权限('查看比赛')) {
        if (time()<$e[starttime])
            异常("比赛尚未开始！",取路径("contest/index.php"));
        if ($d[readforce]>$_SESSION[readforce])
            异常("你没有该场比赛的参与权限！",取路径("contest/index.php"));
        if($_SESSION['ID'] != $_GET['uid'] && time()<$e[endtime])
            $uid = $_SESSION['ID'];
    }
} else 异常("未查询到记录！",取路径("contest/index.php"));
?>

<div class='container-fluid'>
<div class='span4'>
<div class='well'>
<ul class='nav nav-list'>
<li class='nav-header'>比赛：<?=$e['cname']?></li>
<li class=''><?php echo nl2br(sp2n(htmlspecialchars($e['intro']))) ?></li>
<li class=''>比赛状态：<?php
if (time()>$e['endtime']) echo "<span class='did'>已结束</span> <a href='report.php?ctid={$_GET['ctid']}'>查看比赛成绩列表</a>"; else
if (time()<$e['endtime'] && time()>$e['starttime']) echo "<span class='doing'>正在进行</span>"; else
echo "还未开始"; 
?></li>
<li class=''>开始时间：<?=date("Y-m-d H:i:s",$e['starttime']) ?></li>
<li class=''>结束时间：<?=date("Y-m-d H:i:s",$e['endtime']) ?></li>
</ul>
</div>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th width=60px>题目名称</th>
<td><b><?php echo $d['probname']; ?></b>
<? if(time() > $e['endtime'] || 有此权限('查看比赛')) { ?><a href="../problem/problem.php?pid=<?=$_GET['pid']?>">跳转</a><? } ?>
</td></tr>
<tr><th>输入输出</th>
<td><code><?php echo $d['filename']; ?>.in/out</code></td></tr>
<tr><th>时间限制</th>
<td><?php echo $d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?php echo $d['memorylimit']; ?> MiB </td></tr>
<? if(有此权限('查看比赛') || ($_SESSION['ID'] && time() < $e['endtime'] && time() > $e['starttime'])) { ?>
<tr><form action="submit.php" method="post" enctype="multipart/form-data" class='form-inline'>
<td colspan=2>
<input type="file" name="file" title='选择程序源文件' /><br />
<label class='radio inline'><input type='radio' name='lang' value='pas' />Pascal</label>
<label class='radio inline'><input type='radio' name='lang' value='c' />C</label>
<label class='radio inline'><input type='radio' name='lang' value='cpp' checked='checked'/>C++</label>
<button type='submit' class='btn btn-primary pull-right' >提交代码</button>
<input name="pid" type="hidden" value="<?=$d['pid']?>" />
<input name="filename" type="hidden" value="<?=$d['filename']?>" />
<input name="ctid" type="hidden" value="<?=$_GET['ctid']?>" />
<input name="endtime" type="hidden" value="<?=$e['endtime']?>" />
</td></form></tr>
<? } ?>
</table>
<? if (time()>$e['endtime']) { ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th>用户</th>
<th>结果</th>
<th>得分</th>
</tr>
<? $v = $_GET['pid'];
$sql="select * from compscore,userinfo where userinfo.uid=compscore.uid and compscore.pid={$v} and compscore.ctid={$_GET[ctid]} order by compscore.score desc";
$cnt=$r->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $f=$r->rtnrlt($i);
    ?>
<tr>
<td><a href="comp.php?ctid=<?=$_GET['ctid'] ?>&uid=<?=$f['uid'] ?>" target="_blank">
<?=gravatar::showImage($f['email'], 14);?>
<?=有此权限('查看用户') ? $f['realname'] : $f['nickname'] ?>
</a></td>
<td><a href="code.php?csid=<?=$f['csid']?>" target="_blank"><?=评测结果($f['result'])?></a></td>
<td><span class="<?=$f['score']>=100?'ok':'no'?>"><?=$f['score'] ?></span></td>
</tr>
    <?
}
?>
</table>
<? } else { ?>
<?
$sql="select * from compscore,userinfo where compscore.uid='{$uid}' and userinfo.uid='{$uid}' and compscore.pid={$v} and compscore.ctid={$_GET[ctid]}";
$cnt=$r->dosql($sql);
if($cnt) {
    $f=$r->rtnrlt(0);
?>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th width=60px><b><?php echo $f['realname']; ?></b></th>
<td><a href="../user/detail.php?uid=<?=$uid?>"><?php echo $f['nickname']; ?></a></td></tr>
<tr><th>提交时间</th>
<td><?=date('Y-m-d H:i:s',$f['subtime']);?></td></tr>
<tr><th>得分</th>
<td><?=$f['score']; ?></td></tr>
<tr><th>评测结果</th>
<td><?=评测结果($f['result']); ?></td></tr>
<tr><th>代码</th>
<td><a href="code.php?csid=<?=$f[csid] ?>" target="_blank"><?=$STR[lang][$f[lang]] ?></a></td></tr>
</table>
<? } ?>
<? } ?>
</div>
<div class='span8'>
<div class='page'>
<div class='tabbable'>
<ul class='nav nav-tabs'>
<?
    $contains=$e['contains'];
    $pbs=explode(":",$contains);
    $ppid = 1;
    foreach($pbs as $k=>$v) {
        $sql="select probname from problem where pid={$v}";
        $r->dosql($sql);
        $f=$r->rtnrlt(0);
        $pname=$f['probname'];
        $sql="select * from compscore where uid='{$uid}' and compscore.pid={$v} and compscore.ctid={$_GET[ctid]}";
        $cnt=$r->dosql($sql);
        if($cnt) $f=$r->rtnrlt(0);
?>
<li class="<?=($v == $_GET['pid'])?"active":""?>">
<a href="problem.php?pid=<?=$v?>&ctid=<?=$_GET['ctid']?>&uid=<?=$uid?>">
<? echo $ppid . ". "; $ppid++; echo $pname; ?></a>
</li>
<?  } ?>
</ul>
</div>
<?=$d['detail']?>
</div>
</div>
</div>
<?php
    include_once("../include/footer.php");
?>
