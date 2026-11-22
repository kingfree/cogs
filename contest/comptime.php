<?php
require_once("../include/header.php");
gethead(1,"查看比赛","比赛场次评测");
$rounds = ContestRound::withGroup($_GET['ctid']);
$cnt = count($rounds);
if(!$cnt) 异常("未查询到记录！");
$d=($rounds[0] ?? false);
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td width="90px">CTID</td>
    <td><?php echo $d['ctid'] ?></td>
    <td width="90px">关联比赛</td>
    <td><?=h($d['cname'])?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d['starttime']) ?></td>
    <td>结束时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d['endtime']) ?></td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><a href="../user/index.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?=h($d['gname'])?></a></td>
    <td>公布成绩</td>
    <td><?php echo $d['showscore']?"是":"否" ?></td>
  </tr>
  <tr>
    <td>比赛状态</td>
    <td><?php
	 if (time()>$d['endtime']) echo "已结束"; else
	 if (time()<$d['endtime'] && time()>$d['starttime']) echo "正在进行"; else
	 echo "还未开始";
	 ?></td>
    <td>阅读权限</td>
    <td><?php echo $d['readforce'] ?></td>
  </tr>
  <tr>
    <td>查看成绩</td>
    <td><a href="report.php?ctid=<?=$d['ctid']?>" target="_blank">查看</a></td>
    <td>导出代码</td>
    <td>
    <a href="export.php?ctid=<?=$d['ctid']?>" class='btn btn-mini'>导出选手比赛代码</a>
    </td>
  </tr>
  <tr>
    <td>场次介绍</td>
    <td><?php echo BBCode($d['intro']) ?></td>
    <td>修改场次</td>
    <td><a href="editcomptime.php?action=edit&ctid=<?php echo $d['ctid'] ?>">修改</a></td>
  </tr>
</table>
<?php
$entrants = ContestEntry::roundResults($_GET['ctid']);
$rounds = array_column($entrants, 'user');
$cnt = count($rounds);
if ($cnt) {
?>
<form method="post" action="judge.php"><?=formTokenField()?>
    选择评测机：<select name='judger' id='judger'>
    <option value=0 selected=selected>自动选择</option>
<?php
    $judges = Judge::enabled();
    $cnt1 = count($judges);
    foreach ($judges as $i => $e) {
        echo "<option value={$e['grid']} >".h($e['memo'])."</option>";
    }
?>
    </select>
  <input name="do" type="submit" id="do" class='btn' value="评测选定" />
  <input name="do" type="submit" id="do" class='btn' value="评测全部" />
  <input name="ctid" type="hidden" id="ctid" value="<?=h($_GET['ctid'] ?? '')?>" />
</p>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>用户</th>
    <th>提交记录</th>
  </tr>
<?php
	$tu=0;
	foreach ($rounds as $i => $d) {
		if ($d['uid']==$tu) continue;
		$tu=$d['uid'];
?>
  <tr>
    <td><a target="_blank" href="../user/detail.php?uid=<?php echo $d['uid'] ?>"><?php  echo gravatar::showImage($d['email'], 64); echo "<br />"; if(Privilege::allows("查看用户")) echo h($d['realname']); else echo h($d['nickname']);?></a></td>
    <td>

<table class='table table-striped table-condensed table-bordered fiexd'>
	  <tr>
		<th style="width: 5em;">CSID</th>
		<th style="width: 15em;">题目名</th>
		<th style="width: 4em;">代码</th>
		<th style="width: 9em;">提交时间</th>
		<th>测试点</th>
		<th style="width: 4em;">得分</th>
	  </tr>
	<?php
	$judges = array_values($entrants[$d['uid']]['marks']);
	$c = count($judges);
	if ($c) {
		foreach ($judges as $j => $e) {
	?>
        <tr>
        <td>
        <input name="doit[]" type="checkbox" id="doit[]" value="<?php echo $e['csid'] ?>" />
        <input name="doall[]" type="hidden" id="doall[]" value="<?php echo $e['csid'] ?>" />
        <?php echo $e['csid'] ?></td>
        <td><a target="_blank" href="problem.php?ctid=<?=h($_GET['ctid'] ?? '')?>&pid=<?php echo $e['pid'] ?>&uid=<?=$d['uid']?>"><?php echo shortname($e['probname']) ?></a></td>
        <td><a href="code.php?csid=<?php echo $e['csid'] ?>" target="_blank"><?php echo $STR['lang'][$e['lang']] ?></a></td>
        <td><?php echo date("m/d H:i:s",$e['subtime']) ?></td>
        <td><?php echo 评测结果($e['result'], 50) ?></td>
        <td><?php echo $e['score'] ?></td>
        </tr>
        <?php
        }
    }
    ?>
        </table>
        </td>
        <?php
    }
    ?>
  </tr>
</table>
</form>
<?php
}
?>
</div>
<?php
include_once("../include/footer.php");
?>
