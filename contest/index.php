<?php
// 访问 contest/index.php 浏览按阅读等级筛选的场次；进入某场由 contest/problem.php 处理。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","比赛列表");
?>
<div class='row-fluid'>
<?php if(Privilege::allows('修改比赛')) { ?>
<a href="editcompbase.php?action=add" class="btn btn-info pull-left">添加新比赛</a>
<a href="compbase.php?action=add" class="btn btn-info pull-left">比赛场次管理</a>
<?php } ?>
<a href="recent.php" class='btn btn-success'><i class="icon-list-alt icon-white"></i>最近在线竞赛</a>
<?php
$rounds = ContestRound::visible($_SESSION['readforce']); // 列表按阅读等级列场次，实际入场另行核对分组。
$total = count($rounds);
$offset=检测页面($total, ($_GET['page'] ?? 1)); // 每页从该场次偏移量开始。
?>
<div id="nowtime" class='alert alert-success pull-right'>
现在时间：<?=date('Y-m-d H:i:s', time());?>
</div>
<table id="contestlist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
    <th style="width: 10em;">比赛</th>
    <th>场次介绍</th>
    <th style="width: 5ex;">状态</th>
    <th style="width: 5ex;">成绩</th>
    <th style="width: 16ex;">开始时间</th>
    <th style="width: 16ex;">结束时间</th>
    <th style="width: 5em;">开放分组</th>
    <th style="width: 4em;">组织者</th>
<?php if(Privilege::allows('查看比赛')) { ?>
    <th class=admin style="width: 5ex;">评测</th>
<?php  } ?>
<?php if(Privilege::allows('修改比赛')) { ?>
    <th class=admin style="width: 5ex;">比赛</th>
    <th class=admin style="width: 5ex;">场次</th>
<?php  } ?>
</tr></thead>
<?php
for ($i=$offset;$i<$total && $i<$offset+$SET['style_pagesize'] ;$i++) {
		$round=($rounds[$i] ?? false);
?>
<tr>
    <td><b><a href='problem.php?ctid=<?=$round['ctid'] ?>'><?=h($round['cname'])?></a></b></td>
    <td class="wrap"><?=BBCode($round['intro']) ?></td>
    <td ><?php
     echo ['upcoming'=>"<span class='todo'>准备</span>", 'active'=>"<span class='doing'>进行</span>",
         'finished'=>"<span class='did'>结束</span>"][ContestRound::phase($round, time())]; // 列表沿用场次的实际开赛与截止规则。
	 ?></td>
    <td ><?php   if ($round['showscore'] || Privilege::allows('查看比赛'))
                echo "<a href='report.php?ctid=$round[ctid]'>查看</a> ";
            if (!$round['showscore'])
                echo "未";
     ?></td>
    <td ><?=date('Y-m-d H:i', $round['starttime']) ?></td>
    <td ><?=date('Y-m-d H:i', $round['endtime']) ?></td>
    <td ><a href="../user/index.php?gid=<?=$round['gid'] ?>" target="_blank"><?=h($round['gname'])?></a></td>
<td><a href='../user/detail.php?uid=<?=$round['ouid']?>' target='_blank'>
<?php  if(Privilege::allows("查看用户")) echo h($round['realname']); else echo h($round['nickname']);?>
</a></td>
<?php if(Privilege::allows('查看比赛')) { ?>
<td><a href="comptime.php?ctid=<?=$round['ctid']?>">评测</a></td>
<?php  } ?>
<?php if(Privilege::allows('修改比赛')) { ?>
<td><a href="editcompbase.php?action=edit&cbid=<?=$round['cbid']?>"><?=$round['cbid']?></a></td>
<td><a href="editcomptime.php?action=edit&ctid=<?=$round['ctid']?>"><?=$round['ctid']?></a></td>
<?php  } ?>
</tr>
<?php  } ?>
</table>
<?php  分页($total, ($_GET['page'] ?? 1)); ?>
</div>
<?php
	include_once(__DIR__.'/../include/footer.php');
?>
