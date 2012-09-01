<?php
require_once("../include/header.php");
gethead(1,"","在线竞赛");
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead>
<tr>
<th width='160px'>评测系统</th>
<th>竞赛名称</th>
<th width='200px'>开始时间</th>
<th width='100px'>可访问性</th>
</tr>
</thead>
<tbody>
<?php
$json = @file_get_contents('http://contests.acmicpc.info/contests.json');
$rows = json_decode($json, true);
foreach($rows as $row) {
?>
<tr>
<td><?php echo$row['oj']?></td>
<td><a id="name_<?php echo$row['id']?>" href="<?php echo$row['link']?>" target="_blank"><?php echo$row['name']?></a></td>
<td><?php echo$row['start_time']?> <?php echo$row['week']?></td>
<td><?php echo$row['access']?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php
	include_once("../include/footer.php");
?>

