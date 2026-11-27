<?php
// 访问 contest/report.php?ctid=1 查看成绩。ContestRound 决定当前读者可见的场次与题目，ContestEntry 按总分排名。
require_once __DIR__.'/../include/header.php';
$roundId = (int)($_GET['ctid'] ?? 0); // 同一套题单可重复开赛，这里取场次编号。
$round = ContestRound::forReader($roundId); // 成绩表沿用题面的分组、阅读和开赛门槛。
if (!$round || !$round['scoresVisible']) throw new DomainException('比赛成绩不存在或未公布。', 404);
gethead(1, '', '比赛成绩');
$entrants = ContestEntry::rankedResults($round); // 隐藏分数时也保留选手的默认顺序。
$published = $round['otherEntriesVisible']; // 赛后公布或裁判查看时才输出成绩与源码链接。
?>
<div class='row-fluid'>
<table id="contest_report" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
    <th>名次</th><th>用户</th>
<?php $scoreColumn = 1; foreach ($round['problems'] as $problemId => $title) {
    $scoreColumn += 2; // 每题占判定和得分两列，列号从 0 起。
?>
    <th><a href="problem.php?pid=<?=$problemId?>&ctid=<?=$roundId?>" target="_blank"><?=shortname($title)?></a>
    <?php if ($published) { ?><a href="../problem/problem.php?pid=<?=$problemId?>" target="_blank" title="跳转到题目 <?=h($title)?>"><i class='icon-share'></i></a><?php } ?>
    </th>
    <th <?php if ($published) { ?>onclick="sortTable('contest_report', <?=$scoreColumn?>, 'int')" style="cursor:pointer"<?php } ?>>得分</th>
<?php } ?>
    <th <?php if ($published) { ?>onclick="sortTable('contest_report', <?=$scoreColumn + 1?>, 'int')" style="cursor:pointer"<?php } ?>>总分</th>
</tr></thead>
<tbody>
<?php foreach ($entrants as $entrant) {
    $user = $entrant['user']; // 每名选手只有一行，按场次题单找该行各题的交卷。
?>
<tr>
    <td><?=$entrant['rank'] ?? ''?></td>
    <td><a href="../user/detail.php?uid=<?=$user['uid']?>" target="_blank"><?=gravatar::showImage($user['email'], 14)?>
    <?=h(Privilege::allows('查看用户') ? $user['realname'] : $user['nickname'])?></a></td>
<?php foreach ($round['problems'] as $problemId => $title) {
    $entry = $entrant['marks'][$problemId] ?? null; // 没交这道题的选手仍需留一格。
?>
    <td class='wrap'><?php
        if (!$entry || $entry['result'] === 'N') {
            评测信息('N'); // 未提交与已交待评分别显示。
        } elseif ($published) {
            echo "<a href='code.php?csid={$entry['csid']}'>"; // 公开成绩后才能阅读其他选手的原卷。
            if ($entry['result'] === null || $entry['result'] === '') echo '未评测'; else 评测结果($entry['result'], 30);
            echo '</a>';
        } else {
            echo empty($entry['result']) ? '未评测' : '不显示'; // 比赛中只提示是否已经评测。
        } ?></td>
    <td><?=$published ? ($entry['score'] ?? 0) : ''?></td>
<?php } ?>
    <td><?=$published ? $entrant['total'] : ''?></td>
</tr>
<?php } ?>
</tbody></table>
</div>
<?php include_once __DIR__.'/../include/footer.php'; ?>
