<?php
require_once("../include/header.php");
$uid = (int) ($_GET['uid'] ? $_GET['uid'] : $_SESSION['ID']);
$pid = (int) $_GET['pid'];
$ctid = (int) $_GET['ctid'];
if(!$pid && $ctid) {
    $pRows = ContestRound::overview($ctid);
    $cnt = count($pRows);
    if ($cnt) {
        $d=($pRows[0] ?? false);
        $contains=$d['contains'];
    } else i异常("未查询到记录！",取路径("contest/index.php"));
    if (time()<$d['starttime'] && !Privilege::allows('查看比赛'))
        i异常("比赛还未开始，题目暂不公布。",取路径("contest/index.php"));
    else {
        $pbs=explode(":",$contains);
        $pid=(int) $pbs[0];
    }
}
$pRows = Problem::byId($pid);
$cnt = count($pRows);
if ($cnt) {
    $d=($pRows[0] ?? false);
    $rounds = ContestRound::rules($ctid);
    $c = count($rounds);
    $e=($rounds[0] ?? false);

    $subgroup=UserGroup::descendants($e['gid']);
    $subgroup[]=$e['gid'];
    $promise=false;
    foreach($subgroup as $value) {
        if ($value==(int)$_SESSION['group']) {
            $promise=true;
            break;
        }
    }
    if (!$promise && !Privilege::allows('查看比赛'))
        i异常("你没有权限访问该页面！",取路径("contest/index.php"));
    if (!$c)
        i异常("比赛场次不存在！",取路径("contest/index.php"));
    $pbs=explode(":",$e['contains']);
    $pb=0;
    foreach($pbs as $k=>$v) {
        if ($v==$pid)
            $pb=1;
    }
    if (!$pb) { http_response_code(404); exit('本场比赛没有这道题。'); }
    if (!Privilege::allows('查看比赛')) {
        if (time()<$e['starttime'])
            i异常("比赛尚未开始！",取路径("contest/index.php"));
        if ($d['readforce']>$_SESSION['readforce'])
            i异常("你没有该场比赛的参与权限！",取路径("contest/index.php"));
        if($_SESSION['ID'] != $_GET['uid'] && time()<$e['endtime'])
            $uid = $_SESSION['ID'];
    }
} else i异常("未查询到记录！",取路径("contest/index.php"));
$problemTitles = array_column(Problem::selected($pbs), null, 'pid');
$myEntries = array_column(ContestEntry::byEntrant($uid, $ctid), null, 'pid');
gethead(1,"","{$e['cname']} - {$d['pid']}. {$d['probname']}");
require_once __DIR__.'/../include/mathjax.inc.php';
?>

<div class='row-fluid'>
<div id='leftbar' class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th style="width: 5em;">比赛名称</th>
<td><b><?=h($e['cname'])?></b></td>
</tr>
<tr>
<th>比赛状态</th>
<td><?php
if (time()>$e['endtime']) echo "<span class='did'>已结束</span>"; else
if (time()<$e['endtime'] && time()>$e['starttime']) echo "<a href='contest/problem.php?ctid={$e['ctid']}'><span class='doing'>正在进行...</span></a>"; else
echo "<span class='todo'>还未开始</span>";
if(Privilege::allows('查看比赛') || time()>$e['endtime']) echo "<a href='report.php?ctid={$ctid}' target='_blank' class='pull-right'>比赛成绩</a>";
?></td>
</tr>
<tr>
<th>开始时间</th>
<td><?php echo date('Y-m-d H:i:s', $e['starttime']) ?></td>
</tr>
<tr>
<th>结束时间</th>
<td><?php echo date('Y-m-d H:i:s', $e['endtime']) ?></td>
</tr>
<tr>
<th>开放分组</th>
<td><a href="../user/index.php?gid=<?php echo $e['gid'] ?>"><?=h($e['gname'])?></a></td>
</tr>
<tr>
<th>注释介绍</th>
<td class="wrap"><?php echo BBCode($e['intro']) ?></td>
</tr>
</table>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="width: 5em;">题目名称</th>
<td><b><?php echo shortname($d['probname']); ?></b>
<?php  if(time() > $e['endtime'] || Privilege::allows('查看比赛')) { ?><a href="../problem/problem.php?pid=<?=$pid?>" target="_blank" title="跳转到题目 <?=h($d['probname'])?>"><i class='icon-share'></i></a><?php  } ?>
</td></tr>
<tr><th>输入输出</th>
<td><code><?=h($d['filename'])?>.in/out</code></td></tr>
<tr><th>时间限制</th>
<td><?php echo $d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?php echo $d['memorylimit']; ?> MB </td></tr>
<tr><th>测试点数</th>
<td><span class='badge badge-success'><?=$d['datacnt']?></span>
<span class='pull-right'><?=$STR['plugin'][$d['plugin']]?></span>
</td></tr>
<?php  if(Privilege::allows('查看比赛') || ($_SESSION['ID'] && time() < $e['endtime'] && time() > $e['starttime'])) { ?>
<tr><form action="submit.php" method="post" enctype="multipart/form-data" class='form-inline'><?=formTokenField()?>
<td colspan=2>
<input type="file" name="file" title='选择程序源文件' /><br />
<?php  if($d['plugin'] == 3 || $d['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面<b>直接</b>有 <?=$d['datacnt']?> 个 <?=h($d['filename'])?>#.out 文件<br />
<a href="../problem/downinput.php?file=<?=h($d['filename'])?>&data=<?=$d['datacnt']?>" class="btn btn-success btn-mini pull-left">下载测试数据</a>
<?php } else { ?>
<label class='radio inline'><input type='radio' name='lang' value='pas' />Pascal</label>
<label class='radio inline'><input type='radio' name='lang' value='c' />C</label>
<label class='radio inline'><input type='radio' name='lang' value='cpp' checked='checked'/>C++</label>
<?php  } ?>
<button type='submit' class='btn btn-primary pull-right' >提交代码</button>
<input name="pid" type="hidden" value="<?=$d['pid']?>" />
<input name="ctid" type="hidden" value="<?=h($_GET['ctid'] ?? '')?>" />
</td></form></tr>
<?php  } ?>
</table>
<?php  if (time()>$e['endtime']) { ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th>用户</th>
<th>结果</th>
<th>时间</th>
<th>内存</th>
<th>得分</th>
</tr>
<?php
$entries = ContestEntry::standings($pid, $ctid);
$cnt = count($entries);
foreach ($entries as $i => $f) {
    ?>
<tr>
<td><a href="../user/detail.php?uid=<?=$f['uid'] ?>" target="_blank">
<?=gravatar::showImage($f['email'], 14);?>
<?=h(Privilege::allows('查看用户') ? $f['realname'] : $f['nickname'])?>
</a></td>
<td class="wrap"><a href="code.php?csid=<?=$f['csid']?>" target="_blank"><?=评测结果($f['result'], 20)?></a></td>
<td><?php printf("%.3f",$f['runtime']/1000.0) ?></td>
<td><?php printf("%.2f",$f['memory']/1024)?></td>
<td><span class="<?=$f['score']>=100?'ok':'no'?>"><?=$f['score'] ?></span></td>
</tr>
    <?php
}
?>
</table>
<?php  } else { ?>
<?php
$entries = ContestEntry::entrantResult($uid, $uid, $pid, $ctid);
$cnt = count($entries);
if($cnt) {
    $f=($entries[0] ?? false);
?>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="width: 5em;"><b><?=h($f['realname'])?></b></th>
<td><a href="../user/detail.php?uid=<?=$uid?>"><?=h($f['nickname'])?></a></td></tr>
<tr><th>提交时间</th>
<td><?=date('Y-m-d H:i:s',$f['subtime']);?></td></tr>
<tr><th>评测得分</th>
<td><?=$f['score']; ?></td></tr>
<tr><th>评测结果</th>
<td class="wrap"><?=评测结果($f['result'], 60); ?></td></tr>
<tr><th>提交代码</th>
<td><a href="code.php?csid=<?=$f['csid'] ?>" target="_blank"><?=$STR['lang'][$f['lang']] ?></a></td></tr>
</table>
<?php  } ?>
<?php  } ?>
</div>
<div id='rightbar' class='span8'>
<div class='page'>
<div class='tabbable'>
<ul class='nav nav-tabs'>
<?php
    $contains=$e['contains'];
    $pbs=explode(":",$contains);
    $ppid = 1;
    foreach($pbs as $k=>$v) {
        $f = $problemTitles[$v] ?? null;
        $pname=$f['probname'];
        $entries = isset($myEntries[$v]) ? [$myEntries[$v]] : [];
        $cnt = count($entries);
        if($cnt) $f=($entries[0] ?? false);
?>
<li class="<?=($v == $pid)?"active":""?>">
<a href="problem.php?pid=<?=$v?>&ctid=<?=$ctid?>&uid=<?=$uid?>">
<?php  echo $ppid . ". "; $ppid++; echo shortname($pname); ?></a>
</li>
<?php   } ?>
</ul>
</div>
<div class="problem tou">
<a id="chbar" title="隐藏左边栏" class="pull-left" style="cursor:pointer"><i id="chbaricon" class="icon icon-indent-left"></i></a>
<h1><?=$d['pid']?>. <?=shortname($d['probname'])?>
<?php if(Privilege::allows('修改题目')) { ?>
<a href="../problem/editprob.php?action=edit&pid=<?=$d['pid']?>" title="修改题目 <?=h($d['probname'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</h1>
<?=难度($d['difficulty']); ?>&nbsp;&nbsp;
输入文件：<code><?=h($d['filename'])?>.in</code>&nbsp;&nbsp;
输出文件：<code><?=h($d['filename'])?>.out</code>&nbsp;&nbsp;
<?=$STR['plugin'][$d['plugin']]?>
<br />
时间限制：<?=$d['timelimit']/1000?> s&nbsp;&nbsp;
内存限制：<?=$d['memorylimit']; ?> MB
</div>
<dl class='problem'>
<?=richText($d['detail'])?>
</dl>

<?php  if(Privilege::allows('查看比赛') || ($_SESSION['ID'] && time() < $e['endtime'] && time() > $e['starttime'])) { ?>
<form action="submit.php" method="post" enctype="multipart/form-data" class='form-inline' id="tijiao"><?=formTokenField()?>
<td colspan=2>
<input id="source" type="file" name="file" title='选择程序源文件' />
<?php  if($d['plugin'] == 3 || $d['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面<b>直接</b>有 <?=$d['datacnt']?> 个 <?=h($d['filename'])?>#.out 文件
<a href="../problem/downinput.php?file=<?=h($d['filename'])?>&data=<?=$d['datacnt']?>" class="btn btn-success btn-mini">下载测试数据</a>
<?php } else { ?>
<label class='radio inline'><input type='radio' name='lang' value='pas' id='pas'/>Pascal</label>
<label class='radio inline'><input type='radio' name='lang' value='c' id='c'/>C</label>
<label class='radio inline'><input type='radio' name='lang' value='cpp' id='cpp' checked='checked'/>C++</label>
<?php  } ?>
<button type='submit' class='btn btn-primary' >提交代码</button>
<input name="pid" type="hidden" value="<?=$d['pid']?>" />
<input name="ctid" type="hidden" value="<?=h($_GET['ctid'] ?? '')?>" />
</form>
<?php  } ?>

</div>
</div>
</div>
<?php
    include_once("../include/footer.php");
?>
