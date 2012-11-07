<?php
require_once("../include/header.php");
$uid = (int) ($_GET['uid'] ? $_GET['uid'] : $_SESSION['ID']);
$pid = (int) $_GET['pid'];
$ctid = (int) $_GET['ctid'];
$p=new DataAccess();
if(!$pid && $ctid) {
    $sql="select compbase.cname,compbase.contains,comptime.starttime,comptime.endtime,comptime.showscore,comptime.intro,groups.* from comptime,compbase,groups where comptime.cbid=compbase.cbid and comptime.ctid={$ctid} and comptime.group=groups.gid";
    $cnt=$p->dosql($sql);
    if ($cnt) {
        $d=$p->rtnrlt(0);
        $contains=$d['contains'];
    } else 异常("未查询到记录！",取路径("contest/index.php"));
    if (time()<$d['starttime'] && !有此权限('查看比赛')) 
        异常("比赛还未开始，题目暂不公布。",取路径("contest/index.php"));
    else {
        $pbs=explode(":",$contains);
        $pid=(int) $pbs[0];
    }
}
$sql="select * from problem where pid={$pid}";
$cnt=$p->dosql($sql);
if ($cnt) {
    $d=$p->rtnrlt(0);
    $q=new DataAccess();
    $sql="select cname,starttime,endtime,contains,intro,groups.* from compbase,comptime,groups where comptime.cbid=compbase.cbid and comptime.ctid={$ctid} and comptime.group=groups.gid";
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
        if ($v==$pid)
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
gethead(1,"","{$e['cname']} - {$d['pid']}. {$d['probname']}");
$LIB->mathjax();
$r=new DataAccess();
?>

<div class='row-fluid'>
<div id='leftbar' class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th style="width: 5em;">比赛名称</th>
<td><b><?php echo $e[cname] ?></b></td>
</tr>
<tr>
<th>比赛状态</th>
<td><?php
if (time()>$e[endtime]) echo "<span class='did'>已结束</span>"; else
if (time()<$e[endtime] && time()>$e[starttime]) echo "<a href='contest/problem.php?ctid={$e[ctid]}'><span class='doing'>正在进行...</span></a>"; else
echo "<span class='todo'>还未开始</span>"; 
if(有此权限('查看比赛') || time()>$e['endtime']) echo "<a href='report.php?ctid={$ctid}' target='_blank' class='pull-right'>比赛成绩</a>";
?></td>
</tr>
<tr>
<th>开始时间</th>
<td><?php echo date('Y-m-d H:i:s', $e[starttime]) ?></td>
</tr>
<tr>
<th>结束时间</th>
<td><?php echo date('Y-m-d H:i:s', $e[endtime]) ?></td>
</tr>
<tr>
<th>开放分组</th>
<td><a href="../user/index.php?gid=<?php echo $e['gid'] ?>"><?php echo $e['gname'] ?></a></td>
</tr>
<tr>
<th>注释介绍</th>
<td class="wrap"><?php echo BBCode($e[intro]) ?></td>
</tr>
</table>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="width: 5em;">题目名称</th>
<td><b><?php echo shortname($d['probname']); ?></b>
<? if(time() > $e['endtime'] || 有此权限('查看比赛')) { ?><a href="../problem/problem.php?pid=<?=$pid?>" target="_blank" title="跳转到题目 <?=$d['probname']?>"><i class='icon-share'></i></a><? } ?>
</td></tr>
<tr><th>输入输出</th>
<td><code><?php echo $d['filename']; ?>.in/out</code></td></tr>
<tr><th>时间限制</th>
<td><?php echo $d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?php echo $d['memorylimit']; ?> MB </td></tr>
<tr><th>测试点数</th>
<td><span class='badge badge-success'><?=$d['datacnt']?></span>
<span class='pull-right'><?=$STR['plugin'][$d['plugin']]?></span>
</td></tr>
<? if(有此权限('查看比赛') || ($_SESSION['ID'] && time() < $e['endtime'] && time() > $e['starttime'])) { ?>
<tr><form action="submit.php" method="post" enctype="multipart/form-data" class='form-inline'>
<td colspan=2>
<input type="file" name="file" title='选择程序源文件' /><br />
<? if($d['plugin'] == 3 || $d['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面<b>直接</b>有 <?=$d['datacnt']?> 个 <?=$d['filename']?>#.out 文件<br />
<a href="../problem/downinput.php?file=<?=$d['filename']?>&data=<?=$d['datacnt']?>" class="btn btn-success btn-mini pull-left">下载测试数据</a>
<?php } else { ?>
<label class='radio inline'><input type='radio' name='lang' value='pas' />Pascal</label>
<label class='radio inline'><input type='radio' name='lang' value='c' />C</label>
<label class='radio inline'><input type='radio' name='lang' value='cpp' checked='checked'/>C++</label>
<? } ?>
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
<th>时间</th>
<th>内存</th>
<th>得分</th>
</tr>
<?
$sql="select * from compscore,userinfo where userinfo.uid=compscore.uid and compscore.pid={$pid} and compscore.ctid={$ctid} order by compscore.score desc, compscore.runtime asc, compscore.memory asc";
$cnt=$r->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $f=$r->rtnrlt($i);
    ?>
<tr>
<td><a href="../user/detail.php?uid=<?=$f['uid'] ?>" target="_blank">
<?=gravatar::showImage($f['email'], 14);?>
<?=有此权限('查看用户') ? $f['realname'] : $f['nickname'] ?>
</a></td>
<td class="wrap"><a href="code.php?csid=<?=$f['csid']?>" target="_blank"><?=评测结果($f['result'], 10)?></a></td>
<td><?php printf("%.3f",$f['runtime']/1000.0) ?></td>
<td><?php printf("%.2f",$f['memory']/1024)?></td>
<td><span class="<?=$f['score']>=100?'ok':'no'?>"><?=$f['score'] ?></span></td>
</tr>
    <?
}
?>
</table>
<? } else { ?>
<?
$sql="select * from compscore,userinfo where compscore.uid='{$uid}' and userinfo.uid='{$uid}' and compscore.pid={$pid} and compscore.ctid={$ctid}";
$cnt=$r->dosql($sql);
if($cnt) {
    $f=$r->rtnrlt(0);
?>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="width: 5em;"><b><?php echo $f['realname']; ?></b></th>
<td><a href="../user/detail.php?uid=<?=$uid?>"><?php echo $f['nickname']; ?></a></td></tr>
<tr><th>提交时间</th>
<td><?=date('Y-m-d H:i:s',$f['subtime']);?></td></tr>
<tr><th>评测得分</th>
<td><?=$f['score']; ?></td></tr>
<tr><th>评测结果</th>
<td><?=评测结果($f['result']); ?></td></tr>
<tr><th>提交代码</th>
<td><a href="code.php?csid=<?=$f[csid] ?>" target="_blank"><?=$STR[lang][$f[lang]] ?></a></td></tr>
</table>
<? } ?>
<? } ?>
</div>
<div id='rightbar' class='span8'>
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
        $sql="select * from compscore where uid='{$uid}' and compscore.pid={$v} and compscore.ctid={$ctid}";
        $cnt=$r->dosql($sql);
        if($cnt) $f=$r->rtnrlt(0);
?>
<li class="<?=($v == $pid)?"active":""?>">
<a href="problem.php?pid=<?=$v?>&ctid=<?=$ctid?>&uid=<?=$uid?>">
<? echo $ppid . ". "; $ppid++; echo shortname($pname); ?></a>
</li>
<?  } ?>
</ul>
</div>
<div class="problem tou">
<a id="chbar" title="隐藏左边栏" class="pull-left" style="cursor:pointer"><i id="chbaricon" class="icon icon-indent-left"></i></a>
<h1><?=$d['pid']?>. <?=shortname($d['probname'])?>
<?php if(有此权限('修改题目')) { ?>
<a href="../problem/editprob.php?action=edit&pid=<?=$d['pid']?>" title="修改题目 <?=$d['probname']?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</h1>
<?=难度($d['difficulty']); ?>&nbsp;&nbsp;
输入文件：<code><?=$d['filename']?>.in</code>&nbsp;&nbsp;
输出文件：<code><?=$d['filename']?>.out</code>&nbsp;&nbsp;
<?=$STR['plugin'][$d['plugin']]?>
<br />
时间限制：<?=$d['timelimit']/1000?> s&nbsp;&nbsp;
内存限制：<?=$d['memorylimit']; ?> MB
</div>
<dl class='problem'>
<?=$d['detail']?>
</dl>

<? if(有此权限('查看比赛') || ($_SESSION['ID'] && time() < $e['endtime'] && time() > $e['starttime'])) { ?>
<form action="submit.php" method="post" enctype="multipart/form-data" class='form-inline' id="tijiao">
<td colspan=2>
<input id="source" type="file" name="file" title='选择程序源文件' />
<? if($d['plugin'] == 3 || $d['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面<b>直接</b>有 <?=$d['datacnt']?> 个 <?=$d['filename']?>#.out 文件
<a href="../problem/downinput.php?file=<?=$d['filename']?>&data=<?=$d['datacnt']?>" class="btn btn-success btn-mini">下载测试数据</a>
<?php } else { ?>
<label class='radio inline'><input type='radio' name='lang' value='pas' id='pas'/>Pascal</label>
<label class='radio inline'><input type='radio' name='lang' value='c' id='c'/>C</label>
<label class='radio inline'><input type='radio' name='lang' value='cpp' id='cpp' checked='checked'/>C++</label>
<? } ?>
<button type='submit' class='btn btn-primary' >提交代码</button>
<input name="pid" type="hidden" value="<?=$d['pid']?>" />
<input name="filename" type="hidden" value="<?=$d['filename']?>" />
<input name="ctid" type="hidden" value="<?=$_GET['ctid']?>" />
<input name="endtime" type="hidden" value="<?=$e['endtime']?>" />
</form>
<? } ?>

</div>
</div>
</div>
<?php
    include_once("../include/footer.php");
?>
