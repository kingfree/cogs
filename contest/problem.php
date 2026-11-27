<?php
// 从 contest/problem.php?ctid=1 进入场次；未给 pid 时按赛题顺序显示当前读者可见的第一题。
require_once(__DIR__.'/../include/header.php');
$roundId = (int)($_GET['ctid'] ?? 0);
$round = ContestRound::forReader($roundId); // 先检查开赛、分组和阅读门槛，再加载实际题面。
if (!$round) throw new DomainException('场次不存在或未开放。', 404);
$problemId = (int)($_GET['pid'] ?? array_key_first($round['problems'])); // 无题号时取该读者可见的第一题。
if (!isset($round['problems'][$problemId])) throw new DomainException('本场比赛没有这道可读题目。', 404);
$problem = Problem::byId($problemId)[0]; // 场次允许读到尚未在普通题库公开的题。
$entrantId = (int)$_SESSION['ID']; // 赛中默认只显示自己的交卷。
if ($round['otherEntriesVisible'] && !empty($_GET['uid'])) $entrantId = (int)$_GET['uid']; // 赛后公布或裁判查看时，才接受其他参赛者编号。
gethead(1,"","{$round['cname']} - {$problem['pid']}. {$problem['probname']}");
require_once __DIR__.'/../include/mathjax.inc.php';
?>

<div class='row-fluid'>
<div id='leftbar' class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th style="width: 5em;">比赛名称</th>
<td><b><?=h($round['cname'])?></b></td>
</tr>
<tr>
<th>比赛状态</th>
<td><?php
if ($round['phase'] === 'finished') echo "<span class='did'>已结束</span>"; else
if ($round['phase'] === 'active') echo "<a href='problem.php?ctid={$round['ctid']}'><span class='doing'>正在进行...</span></a>"; else
echo "<span class='todo'>还未开始</span>";
if($round['scoresVisible']) echo "<a href='report.php?ctid={$roundId}' target='_blank' class='pull-right'>比赛成绩</a>";
?></td>
</tr>
<tr>
<th>开始时间</th>
<td><?php echo date('Y-m-d H:i:s', $round['starttime']) ?></td>
</tr>
<tr>
<th>结束时间</th>
<td><?php echo date('Y-m-d H:i:s', $round['endtime']) ?></td>
</tr>
<tr>
<th>开放分组</th>
<td><a href="../user/index.php?gid=<?php echo $round['gid'] ?>"><?=h($round['gname'])?></a></td>
</tr>
<tr>
<th>注释介绍</th>
<td class="wrap"><?php echo BBCode($round['intro']) ?></td>
</tr>
</table>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="width: 5em;">题目名称</th>
<td><b><?php echo shortname($problem['probname']); ?></b>
<?php  if($round['phase'] === 'finished' || Privilege::allows('查看比赛')) { ?><a href="../problem/problem.php?pid=<?=$problemId?>" target="_blank" title="跳转到题目 <?=h($problem['probname'])?>"><i class='icon-share'></i></a><?php  } ?>
</td></tr>
<tr><th>输入输出</th>
<td><code><?=h($problem['filename'])?>.in/out</code></td></tr>
<tr><th>时间限制</th>
<td><?php echo $problem['timelimit']; ?> ms (<?=$problem['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?php echo $problem['memorylimit']; ?> MB </td></tr>
<tr><th>测试点数</th>
<td><span class='badge badge-success'><?=$problem['datacnt']?></span>
<span class='pull-right'><?=$STR['plugin'][$problem['plugin']]?></span>
</td></tr>
<?php  if($round['acceptsSubmissions']) { ?>
<tr><form action="submit.php" method="post" enctype="multipart/form-data" class='form-inline'><?=formTokenField()?>
<td colspan=2>
<input type="file" name="file" title='选择程序源文件' /><br />
<?php  if($problem['plugin'] == 3 || $problem['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面<b>直接</b>有 <?=$problem['datacnt']?> 个 <?=h($problem['filename'])?>#.out 文件<br />
<a href="../problem/downinput.php?file=<?=h($problem['filename'])?>&data=<?=$problem['datacnt']?>" class="btn btn-success btn-mini pull-left">下载测试数据</a>
<?php } else { ?>
<?php renderLanguageChoices(false); ?>
<?php  } ?>
<button type='submit' class='btn btn-primary pull-right' >提交代码</button>
<input name="pid" type="hidden" value="<?=$problem['pid']?>" />
<input name="ctid" type="hidden" value="<?=$roundId?>" />
</td></form></tr>
<?php  } ?>
</table>
<?php  if ($round['otherEntriesVisible'] && $round['phase'] === 'finished') { ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th>用户</th>
<th>结果</th>
<th>时间</th>
<th>内存</th>
<th>得分</th>
</tr>
<?php
$entries = ContestEntry::standings($problemId, $roundId);
foreach ($entries as $entry) { // 按得分、耗时和内存显示本题的公开成绩。
    ?>
<tr>
<td><a href="../user/detail.php?uid=<?=$entry['uid'] ?>" target="_blank">
<?=gravatar::showImage($entry['email'], 14);?>
<?=h(Privilege::allows('查看用户') ? $entry['realname'] : $entry['nickname'])?>
</a></td>
<td class="wrap"><a href="code.php?csid=<?=$entry['csid']?>" target="_blank"><?=评测结果($entry['result'], 20)?></a></td>
<td><?php printf("%.3f",$entry['runtime']/1000.0) ?></td>
<td><?php printf("%.2f",$entry['memory']/1024)?></td>
<td><span class="<?=$entry['score']>=100?'ok':'no'?>"><?=$entry['score'] ?></span></td>
</tr>
    <?php
}
?>
</table>
<?php  } else { ?>
<?php
$entry = ContestEntry::entrantResult($entrantId, $problemId, $roundId)[0] ?? null; // 成绩未开放时只读当前选手的这一题。
if ($entry) {
?>
<table id="probinfo" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="width: 5em;"><b><?=h($entry['realname'])?></b></th>
<td><a href="../user/detail.php?uid=<?=$entrantId?>"><?=h($entry['nickname'])?></a></td></tr>
<tr><th>提交时间</th>
<td><?=date('Y-m-d H:i:s',$entry['subtime']);?></td></tr>
<tr><th>评测得分</th>
<td><?=$round['scoresVisible'] ? $entry['score'] : '未公布'?></td></tr>
<tr><th>评测结果</th>
<td class="wrap"><?php if ($round['scoresVisible']) 评测结果($entry['result'], 60); else echo '未公布'; ?></td></tr>
<tr><th>提交代码</th>
<td><a href="code.php?csid=<?=$entry['csid'] ?>" target="_blank"><?=$STR['lang'][$entry['lang']] ?></a></td></tr>
</table>
<?php  } ?>
<?php  } ?>
</div>
<div id='rightbar' class='span8'>
<div class='page'>
<div class='tabbable'>
<ul class='nav nav-tabs'>
<?php
    $position = 1; // 题面标签显示场内顺序，从第一道可读题开始。
    foreach ($round['problems'] as $listedId => $title) {
?>
<li class="<?=$listedId == $problemId ? 'active' : ''?>">
<a href="problem.php?pid=<?=$listedId?>&ctid=<?=$roundId?>&uid=<?=$entrantId?>">
<?php  echo $position . ". "; $position++; echo shortname($title); ?></a>
</li>
<?php   } ?>
</ul>
</div>
<div class="problem tou">
<a id="chbar" title="隐藏左边栏" class="pull-left" style="cursor:pointer"><i id="chbaricon" class="icon icon-indent-left"></i></a>
<h1><?=$problem['pid']?>. <?=shortname($problem['probname'])?>
<?php if(Privilege::allows('修改题目')) { ?>
<a href="../problem/editprob.php?action=edit&pid=<?=$problem['pid']?>" title="修改题目 <?=h($problem['probname'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</h1>
<?=难度($problem['difficulty']); ?>&nbsp;&nbsp;
输入文件：<code><?=h($problem['filename'])?>.in</code>&nbsp;&nbsp;
输出文件：<code><?=h($problem['filename'])?>.out</code>&nbsp;&nbsp;
<?=$STR['plugin'][$problem['plugin']]?>
<br />
时间限制：<?=$problem['timelimit']/1000?> s&nbsp;&nbsp;
内存限制：<?=$problem['memorylimit']; ?> MB
</div>
<dl class='problem'>
<?=richText($problem['detail'])?>
</dl>

<?php  if($round['acceptsSubmissions']) { ?>
<form action="submit.php" method="post" enctype="multipart/form-data" class='form-inline' id="tijiao"><?=formTokenField()?>
<td colspan=2>
<input id="source" type="file" name="file" title='选择程序源文件' />
<?php  if($problem['plugin'] == 3 || $problem['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面<b>直接</b>有 <?=$problem['datacnt']?> 个 <?=h($problem['filename'])?>#.out 文件
<a href="../problem/downinput.php?file=<?=h($problem['filename'])?>&data=<?=$problem['datacnt']?>" class="btn btn-success btn-mini">下载测试数据</a>
<?php } else { ?>
<?php renderLanguageChoices(true); ?>
<?php  } ?>
<button type='submit' class='btn btn-primary' >提交代码</button>
<input name="pid" type="hidden" value="<?=$problem['pid']?>" />
<input name="ctid" type="hidden" value="<?=$roundId?>" />
</form>
<?php  } ?>

</div>
</div>
</div>
<?php
    include_once(__DIR__.'/../include/footer.php');
?>
