<?php
// 访问 contest/index.php 浏览按阅读等级筛选的场次；进入某场由 contest/problem.php 处理。
require_once("../include/header.php");
gethead(1,"","比赛列表");
?>
<div class='row-fluid'>
<?php if(Privilege::allows('修改比赛')) { ?>
<a href="editcompbase.php?action=add" class="btn btn-info pull-left">添加新比赛</a>
<a href="compbase.php?action=add" class="btn btn-info pull-left">比赛场次管理</a>
<?php } ?>
<a href="recent.php" class='btn btn-success'><i class="icon-list-alt icon-white"></i>最近在线竞赛</a>
<?php
$rounds = ContestRound::visible($_SESSION['readforce']);
$cnt = count($rounds);
$st=检测页面($cnt, ($_GET['page'] ?? 1));
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
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
		$d=($rounds[$i] ?? false);
?>
<tr>
    <td><b><a href='problem.php?ctid=<?=$d['ctid'] ?>'><?=h($d['cname'])?></a></b></td>
    <td class="wrap"><?=BBCode($d['intro']) ?></td>
    <td ><?php
	 if (time()>$d['endtime']) echo "<span class='did'>结束</span>"; else
	 if (time()<$d['endtime'] && time()>$d['starttime']) echo "<span class='doing'>进行</span>"; else
	 echo "<span class='todo'>准备</span>"; 
	 ?></td>
    <td ><?php   if ($d['showscore'] || $_SESSION['admin'])
                echo "<a href='report.php?ctid=$d[ctid]'>查看</a> ";
            if (!$d['showscore'])
                echo "未";
     ?></td>
    <td ><?=date('Y-m-d H:i', $d['starttime']) ?></td>
    <td ><?=date('Y-m-d H:i', $d['endtime']) ?></td>
    <td ><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=h($d['gname'])?></a></td>
<td><a href='../user/detail.php?uid=<?=$d['ouid']?>' target='_blank'>
<?php  if(Privilege::allows("查看用户")) echo h($d['realname']); else echo h($d['nickname']);?>
</a></td>
<?php if(Privilege::allows('查看比赛')) { ?>
<td><a href="comptime.php?ctid=<?=$d['ctid']?>">评测</a></td>
<?php  } ?>
<?php if(Privilege::allows('修改比赛')) { ?>
<td><a href="editcompbase.php?action=edit&cbid=<?=$d['cbid']?>"><?=$d['cbid']?></a></td>
<td><a href="editcomptime.php?action=edit&ctid=<?=$d['ctid']?>"><?=$d['ctid']?></a></td>
<?php  } ?>
</tr>
<?php  } ?>
</table>
<?php  分页($cnt, ($_GET['page'] ?? 1)); ?>
</div>
<?php
	include_once("../include/footer.php");
?>
