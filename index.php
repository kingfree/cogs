<?php
// 站点首页。访问 index.php 聚合当前读者能看到的近期题目和提交；每栏数量来自站点设置。
// 例：GET /index.php；子目录部署时访问站点前缀下的 index.php。
require_once(__DIR__.'/include/header.php');
$mySubmissions = Submission::bestByProblem($_SESSION['ID']);
gethead(1,"","首页");

?>
<div class='row-fluid'>
<div class='span9'>
<div class='row-fluid'>
<div class='span4' id="index_page">
<?php
$now = time();
$rounds = ContestRound::upcomingBetween($now, $now + 2*3600);        // 首页优先展示正在进行或两小时内开始的比赛。
if ($rounds) {
    foreach ($rounds as $round) {
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th style="min-width: 3em;">比赛</th>
<td><b><?=h($round['cname'])?></b></td>
</tr>
<tr>
<th>状态</th>
<td><?php
$phase = ContestRound::phase($round, $now);                         // 开始秒和截止秒沿用比赛页的判据。
if ($phase === 'active') echo "<a href='contest/problem.php?ctid={$round['ctid']}'><span class='doing'>正在进行...</span></a>";
elseif ($phase === 'finished') echo "<span class='did'>已结束</span>";
else echo "<span class='todo'>还未开始</span>";
?></td>
</tr>
<tr>
<th>开始</th>
<td><?php echo date('Y-m-d H:i:s', $round['starttime']) ?></td>
</tr>
<tr>
<th>结束</th>
<td><?php echo date('Y-m-d H:i:s', $round['endtime']) ?></td>
</tr>
<tr>
<th>分组</th>
<td><a href="user/index.php?gid=<?php echo $round['gid'] ?>"><?=h($round['gname'])?></a></td>
</tr>
<tr>
<th>介绍</th>
<td><?php echo BBCode($round['intro']) ?></td>
</tr>
</table>
<?php } ?>
<br />
<?php  } else { ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php
foreach (Page::recent() as $page) {                                 // 当前没有比赛时用最新公告填充左栏。
?>
<tr><td><a href="page/page.php?aid=<?=$page['aid']?>" title="<?=h($page['title'])?>"><?=shortname($page['title'])?></a></td></tr>
<?php } ?>
</table>
<?php  } ?>
</div>
<div class='span8' id="index_title">
<div>
<?=输出文本($SET['global_bulletin']); ?>
</div>
<div id='index'>
<?php echo 输出文本($SET['global_head']); ?>
</div>
<div>
<?php echo 输出文本($SET['global_index']); ?>
</div>
</div>
</div>
<div class='row-fluid'>
<div class='span4' id="index_problem">
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead>
<tr>
<th style="min-width: 4ex;">PID</th>
<th>最新题目
<a href="problem/random.php" title="随机选择一道你没有通过的题目" class='btn btn-danger btn-mini pull-right' ><i class='icon icon-random icon-white'></i></a>
</th>
</thead>
</tr>
<?php
foreach (Problem::recent($SET['index_problem_size']) as $problem) {    // 最近开放的可读题目配上本人的答题标记。
?>
<tr>
<td><?php echo $problem['pid']; ?></td>
<td><?php  是否通过($mySubmissions[$problem['pid']] ?? null); ?>
<a href="problem/problem.php?pid=<?php echo $problem['pid']; ?>" title="<?=h($problem['probname'])?>"><?php echo shortname($problem['probname']); ?></a></td>
</tr>
<?php } ?>
</table>
</div>
<div class='span8' id="index_submit">
<?php
$canReview = Privilege::allows('修改题目') && Privilege::allows('查看题目');   // 审题人看待审清单，投稿人只看自己的草稿。
$drafts = $canReview ? Problem::awaitingReview() : Problem::drafts((int)$_SESSION['ID']);
$submissionLimit = max(0, (int)$SET['index_submit_size'] - count($drafts));  // 待审清单和高分提交共用首页行数。
if ($drafts) {
?>
<div class="alert">
<?php if ($canReview) { ?><span class="pull-right">（其实只是设置为不可提交的题目）</span><?php } ?>
<h4><?=$canReview ? '用户提交待审核题目：' : '已提交正在审核的题目：'?></h4><ul>
<?php foreach ($drafts as $draft) { ?>
<li><a href="problem/problem.php?pid=<?=$draft['pid']?>"><?=$draft['pid']?>. <?=h($draft['probname'])?></a>
<a href="problem/editprob.php?action=edit&amp;pid=<?=$draft['pid']?>">[编辑]</a></li>
<?php } ?>
</ul></div>
<?php } ?>
<table class='table table-striped table-condensed table-bordered fixed'>
<thead><tr>
<th>题目</th>
<th>用户</th>
<th style="min-width: 12ex;">评测结果</th>
<th style="min-width: 5ex;">得分</th>
<th style="min-width: 10ex;">提交时间</th>
</tr></thead>
<?php
foreach (Submission::highScores($SET['index_submit_score'], $submissionLimit) as $submission) {
?>
<tr>
<td class="wrap"><?php if (empty($_GET['pid'])) {
    是否通过($mySubmissions[$submission['pid']] ?? null);
    echo "<a href='submit/?pid={$submission['pid']}'>".shortname($submission['probname'])."</a>";
    echo "<a href='problem/problem.php?pid={$submission['pid']}' title='".h($submission['probname'])."' target='_blank'><span class='icon-share'></span></a>";
} else
    echo "<a href='problem/problem.php?pid={$submission['pid']}' title='".h($submission['probname'])."' target='_blank'>".shortname($submission['probname'])."</a>";
?></td>
<td class="wrap"><a href='user/detail.php?uid=<?=$submission['uid']?>' title="<?=(sp2n(htmlspecialchars($submission['memo'])))?>" target='_blank'><?=gravatar::showImage($submission['email']);?></a>
<?php echo "<a href='submit/?uid={$submission['uid']}'>";
if(Privilege::allows("查看用户")) echo h($submission['realname']); else echo h($submission['nickname']);
echo "</a>"; ?></td>
<td><a href='submit/code.php?id=<?=$submission['sid']?>' title="<?=h($submission['result'])?>"><?=评测结果($submission['result'], 10, true)?></a></td>
<td><span class="<?=$submission['accepted']?'ok':'no'?>"><?=$submission['score'] ?></span></td>
<td><?php echo date('H:i:s',$submission['subtime']); ?></td>
</tr>
<?php } ?>
</table>
</div>
</div>
</div>
<div class='span3' id="index_user">
<table class='table table-striped table-condensed table-bordered fixed'>
<thead>
<tr>
<th style="width: 2ex;"></th>
<th>用户排名</th>
<th style="width: 5ex;">积分</th>
<th style="width: 5ex;">题目</th>
</tr>
</thead>
<?php
foreach (Account::leaders($SET['style_ranksize']) as $position => $leader) {  // 名次依排行榜查询的先后从 1 计数。
?>
<tr>
<td><i><?=$position+1 ?></i></td>
<td><a href="user/detail.php?uid=<?php echo $leader['uid']; ?>" title="<?=(sp2n(htmlspecialchars($leader['memo'])))?>"><?=gravatar::showImage($leader['email'], $SET['index_rank_icon']);?><?php if(Privilege::allows("查看用户")) echo h($leader['realname']); else echo h($leader['nickname']);?></a></td>
<td><?=$leader['grade']?></td>
<td><?=$leader['accepted']?></td>
</tr>
<?php } ?>
</table>
</div>
</div>
<?php
require_once(__DIR__.'/include/footer.php');
?>
