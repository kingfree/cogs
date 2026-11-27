<?php
// 访问 contest/recent.php 查看外站赛程；本页的数据来自远端赛事列表。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","在线竞赛");
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead>
<tr>
<th min-width='160px'>评测系统</th>
<th>竞赛名称</th>
<th min-width='200px'>开始时间</th>
<th min-width='100px'>可访问性</th>
</tr>
</thead>
<tbody>
<?php
$events = Contest::recentElsewhere(); // 外站不可用时仍可进入比赛页面。
if (!$events) echo '<tr><td colspan="4">暂无外站赛程。</td></tr>';
foreach ($events as $event) {
?>
<tr>
<td><?=h($event['oj'] ?? '')?></td>
<td><a href="<?=h($event['link'])?>" target="_blank" rel="noopener"><?=h($event['name'])?></a></td>
<td><?=h($event['start_time'] ?? '')?> <?=h($event['week'] ?? '')?></td>
<td><?=h($event['access'] ?? '')?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php
	include_once(__DIR__.'/../include/footer.php');
?>

