<?php
// 管理员从 contest/comptime.php?ctid=1 选择待评交卷；这里的编号代表具体开赛场次。
require_once(__DIR__.'/../include/header.php');
gethead(0, '查看比赛', ''); // 在加载管理表单前核对访问权限。
$round = ContestRound::withGroup((int)($_GET['ctid'] ?? 0))[0] ?? null; // 管理页按具体场次取排期和分组。
if (!$round) throw new DomainException('场次不存在。', 404);
gethead(1, '', '比赛场次评测');
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td width="90px">CTID</td>
    <td><?php echo $round['ctid'] ?></td>
    <td width="90px">关联比赛</td>
    <td><?=h($round['cname'])?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date('Y-m-d H:i:s', $round['starttime']) ?></td>
    <td>结束时间</td>
    <td><?php echo date('Y-m-d H:i:s', $round['endtime']) ?></td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><a href="../user/index.php?gid=<?php echo $round['gid'] ?>" target="_blank"><?=h($round['gname'])?></a></td>
    <td>公布成绩</td>
    <td><?php echo $round['showscore']?"是":"否" ?></td>
  </tr>
  <tr>
    <td>比赛状态</td>
    <td><?php
     echo ['upcoming'=>'还未开始', 'active'=>'正在进行', 'finished'=>'已结束'][ContestRound::phase($round, time())]; // 裁判看到的状态与选手交卷规则一致。
	 ?></td>
    <td>阅读权限</td>
    <td><?php echo $round['readforce'] ?></td>
  </tr>
  <tr>
    <td>查看成绩</td>
    <td><a href="report.php?ctid=<?=$round['ctid']?>" target="_blank">查看</a></td>
    <td>导出代码</td>
    <td>
    <a href="export.php?ctid=<?=$round['ctid']?>" class='btn btn-mini'>导出选手比赛代码</a>
    </td>
  </tr>
  <tr>
    <td>场次介绍</td>
    <td><?php echo BBCode($round['intro']) ?></td>
    <td>修改场次</td>
    <td><a href="editcomptime.php?action=edit&ctid=<?php echo $round['ctid'] ?>">修改</a></td>
  </tr>
</table>
<?php
$entrants = ContestEntry::roundResults($round['ctid']); // 各选手的交卷已按题目分组，可直接生成待评清单。
if ($entrants) {
?>
<form method="post" action="judge.php"><?=formTokenField()?>
    选择评测机：<select name='judger' id='judger'>
    <option value=0 selected=selected>自动选择</option>
<?php
    foreach (Judge::enabled() as $judge) { // 仅允许选择站点当前启用的机位。
        echo "<option value={$judge['grid']} >".h($judge['memo'])."</option>";
    }
?>
    </select>
  <input name="do" type="submit" id="do" class='btn' value="评测选定" />
  <input name="do" type="submit" id="do" class='btn' value="评测全部" />
  <input name="ctid" type="hidden" id="ctid" value="<?=$round['ctid']?>" />
</p>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>用户</th>
    <th>提交记录</th>
  </tr>
<?php
    foreach ($entrants as $entrant) {
        $user = $entrant['user']; // 用户信息只显示一次，下方逐题列出其交卷。
?>
  <tr>
    <td><a target="_blank" href="../user/detail.php?uid=<?php echo $user['uid'] ?>"><?php  echo gravatar::showImage($user['email'], 64); echo "<br />"; if(Privilege::allows("查看用户")) echo h($user['realname']); else echo h($user['nickname']);?></a></td>
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
    foreach ($entrant['marks'] as $entry) { // 选中格和全选格提交同一个 csid。
	?>
        <tr>
        <td>
        <input name="doit[]" type="checkbox" id="doit[]" value="<?php echo $entry['csid'] ?>" />
        <input name="doall[]" type="hidden" id="doall[]" value="<?php echo $entry['csid'] ?>" />
        <?php echo $entry['csid'] ?></td>
        <td><a target="_blank" href="problem.php?ctid=<?=$round['ctid']?>&pid=<?php echo $entry['pid'] ?>&uid=<?=$user['uid']?>"><?php echo shortname($entry['probname']) ?></a></td>
        <td><a href="code.php?csid=<?php echo $entry['csid'] ?>" target="_blank"><?php echo $STR['lang'][$entry['lang']] ?></a></td>
        <td><?php echo date("m/d H:i:s",$entry['subtime']) ?></td>
        <td><?php echo 评测结果($entry['result'], 50) ?></td>
        <td><?php echo $entry['score'] ?></td>
        </tr>
        <?php
    }
    ?>
        </table>
        </td></tr>
        <?php } ?>
</table>
</form>
<?php
}
?>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
