<?php
/**
 * GET /user/detail.php?uid=2 展示资料、参赛记录和答题历史。也可以通过 ?user=用户名 搜索。
 * 阅读范围取决于访问者，换组后连本人旧提交也会按新的题目阅读范围筛选。
 */
require_once(__DIR__.'/../include/header.php');
$uid = (int)($_GET['uid'] ?? 0);
$profiles = $uid ? Account::profile($uid) : Account::findProfile($_GET['user'] ?? ''); // 链接按编号查，手动搜索允许使用用户名或昵称。
$profile = $profiles[0] ?? null;
if (!$profile) 异常('无此用户！', 取路径('user/index.php'));
$uid = (int)$profile['uid']; // 搜索命中后，参赛、提交和导出链接统一使用实际的用户编号。
$maySeePrivateDetails = Privilege::allows('查看用户') || (int)$_SESSION['ID'] === $uid; // 本人及具有查看用户权限的维护者能阅读真实姓名和登录 IP。
gethead(1, '', $profile['nickname'], $uid);
?>
<div class='row-fluid'>
<div class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th style="width: 5em;">用户编号</th>
<td><?=$profile['uid'] ?></td>
</tr>
<tr>
<th>用户名称</th>
<td><?=h($profile['usr'])?></td>
</tr>
<tr>
<th>用户头像
<p>&nbsp;</p>
<?php if($_SESSION['ID'] == $uid) { ?>
<a href="../mail" class="btn btn-success" title="信件"><span class='icon-envelope icon-white'></span></a>
<p style="line-height: 2px;">&nbsp;</p>
<a href="panel.php" class="btn btn-primary" title="设置"><span class='icon-cog icon-white'></span></a>
<?php  } ?></th>
<td><?=gravatar::showImage($profile['email'], 200);?></td>
</tr>
<tr>
<th>用户昵称</th>
<td><?=h($profile['nickname'])?></td>
</tr>
<tr>
<th>E-mail</th>
<td><a href="mailto:<?=h($profile['email'])?>" target="_blank"><?=h($profile['email'])?></a></td>
</tr>
<tr>
<th>阅读权限</th>
<td><?=$profile['readforce'] ?></td>
</tr>
<tr>
<th>所属分组</th>
<td><a href="../user/index.php?gid=<?=$profile['gid'] ?>" class='btn btn-mini btn-warning'><?=h($profile['gname'])?></a></td>
</tr>
<tr>
<th>等级积分</th>
<td><?=$profile['grade'] ?></td>
</tr>
<tr>
<th>注册时间</th>
<td><?=date('Y-m-d H:i:s', $profile['regtime']) ?></td>
</tr>
<tr>
<th>个人介绍</th>
<td class='wrap'><?=BBCode($profile['memo']) ?></td>
</tr>
  <?php if($maySeePrivateDetails) { ?>
  <tr>
    <th>真实姓名</th>
    <td><?=h($profile['realname'])?></td>
  </tr>
  <tr>
    <th>用户权限</th>
    <td>
  <?php
    $privileges = Privilege::forUser($profile['uid']); // 普通用户未必有显式授权，下面单独处理其默认身份。
    if (!$privileges) echo h(array_search(0, $pri)).' '; // 新注册用户没有单独授权，显示普通用户。
    foreach ($privileges as $grant) {
        echo h(array_search($grant['pri'], $pri)).' ';
    }
  ?>
    </td>
  </tr>
  <tr>
    <th>登录 IP</th>
    <td><a href="loginlog.php?uid=<?=$profile['uid']?>"><?=h($profile['lastip'])?></a></td>
  </tr>
  <?php } ?>
</table>
<?php
$rounds = ContestRound::forUser($uid); // 比赛日程可能有多场，个人页下方按比赛配置展示。
if ($rounds) {
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr><th>比赛名</th><th>参加时间</th></tr>
<?php
// 同一比赛配置可能参加多场，个人主页沿用按配置去重的展示方式。
$listedContests = [];
foreach ($rounds as $round) {
    if (isset($listedContests[$round['cbid']])) continue;
    $listedContests[$round['cbid']] = true; // 记录已展示的配置，避免重复历史场次。
?>
<tr>
<td><b><?=h($round['cname'])?></b></td>
<td><a href="../contest/problem.php?ctid=<?=$round['ctid']?>" target="_blank"><?=date('Y-m-d', $round['subtime'])?></a></td>
</tr>
<?php } ?>
</table>
<?php } ?>
</div>
<div class='span8'>
<a href="../submit/index.php?uid=<?=$uid?>" target="_blank">查看全部提交记录</a>
<?php if($maySeePrivateDetails) { ?>
<a href="export.php?uid=<?=$uid?>" target="_blank" class='btn pull-right'>导出全部提交记录</a>
<?php  } ?>
<?php
// 一题可能提交多次：通过数按题目去重，提交次数则统计每一份当前可见的记录。
$history = Submission::history($uid);
$totalSubmissions = count($history); // 分母保留同一题的多次尝试。
$listedProblems = []; // 按题号收集最高分的代表提交，用于每题只展示一次。
$acceptedProblems = 0;
foreach ($history as $submission) {
    if (isset($listedProblems[$submission['pid']])) continue; // 同题后续提交仍计入总次数，但不再占一个展示单元。
    $listedProblems[$submission['pid']] = $submission; // 每题取查询结果中最高分的代表提交。
    if ($submission['accepted']) $acceptedProblems++; // 通过数量按题计，不因重复提交增长。
}
if ($listedProblems) {
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php foreach (array_chunk($listedProblems, 4) as $row) { ?>
<tr>
<?php foreach ($row as $submission) { ?>
<td>
<a href="../submit/code.php?id=<?=$submission['sid']?>" target="_blank">
<?=h($STR['lng'][$submission['lang']])?>
<i class="icon-<?=$submission['accepted'] ? 'ok' : 'remove'?>"></i>
</a>
<a href="../problem/problem.php?pid=<?=$submission['pid']?>" target="_blank"><?=shortname($submission['probname'])?></a>
</td>
<?php } ?>
</tr>
<?php } ?>
</table>
<?php } ?>
<p class="alert">
通过了<b><?=$acceptedProblems?></b>道题，一共提交了<b><?=$totalSubmissions?></b>次，通过率为<b><?=number_format($totalSubmissions ? $acceptedProblems / $totalSubmissions * 100 : 0, 2)?></b>%。
</p>
</div>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
