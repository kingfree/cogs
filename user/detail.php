<?php
/**
 * GET /user/detail.php?uid=2 展示资料、参赛记录和答题历史。也可以通过 ?user=用户名 搜索。
 * 阅读范围取决于访问者，换组后连本人旧提交也会按新的题目阅读范围筛选。
 */
require_once("../include/header.php");
$uid = (int) $_GET['uid'];
$user = $_GET['user'];
if($uid)
$pRows = Account::profile($uid);
else
$pRows = Account::findProfile($user, $user, $user, $user);
$cnt = count($pRows);
if(!$cnt) 异常("无此用户！", 取路径("user/index.php"));
$d=($pRows[0] ?? false);
$uid=$d['uid'];
gethead(1,"","{$d['nickname']}", $uid);
?>
<div class='row-fluid'>
<div class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th style="width: 5em;">用户编号</th>
<td><?=$d['uid'] ?></td>
</tr>
<tr>
<th>用户名称</th>
<td><?=h($d['usr'])?></td>
</tr>
<tr>
<th>用户头像
<p>&nbsp;</p>
<?php if($_SESSION['ID'] == $uid) { ?>
<a href="../mail" class="btn btn-success" title="信件"><span class='icon-envelope icon-white'></span></a>
<p style="line-height: 2px;">&nbsp;</p>
<a href="panel.php" class="btn btn-primary" title="设置"><span class='icon-cog icon-white'></span></a>
<?php  } ?></th>
<td><?=gravatar::showImage($d['email'], 200);?></td>
</tr>
<tr>
<th>用户昵称</th>
<td><?=h($d['nickname'])?></td>
</tr>
<tr>
<th>E-mail</th>
<td><a href="mailto:<?=h($d['email'])?>" target="_blank"><?=h($d['email'])?></a></td>
</tr>
<tr>
<th>阅读权限</th>
<td><?=$d['readforce'] ?></td>
</tr>
<tr>
<th>所属分组</th>
<td><a href="../user/index.php?gid=<?=$d['gid'] ?>" class='btn btn-mini btn-warning'><?=h($d['gname'])?></a></td>
</tr>
<tr>
<th>等级积分</th>
<td><?=$d['grade'] ?></td>
</tr>
<tr>
<th>注册时间</th>
<td><?=date('Y-m-d H:i:s', $d['regtime']) ?></td>
</tr>
<tr>
<th>个人介绍</th>
<td class='wrap'><?=BBCode($d['memo']) ?></td>
</tr>
  <?php if(Privilege::allows('查看用户') || $_SESSION['ID']==$d['uid']) { ?>
  <tr>
    <th>真实姓名</th>
    <td><?=h($d['realname'])?></td>
  </tr>
  <tr>
    <th>用户权限</th>
    <td>
  <?php
    $privileges = Privilege::ordered($d['uid']);
	$cnt = count($privileges);
    if(!$cnt) echo array_search(0,$pri) . " ";
	foreach ($privileges as $i => $e) {
        echo array_search($e['pri'],$pri) . " ";
    }
  ?>
    </td>
  </tr>
  <tr>
    <th>登录 IP</th>
    <td><a href="loginlog.php?uid=<?=$d['uid']?>"><?=$d['lastip'] ?></a></td>
  </tr>
  <?php } ?>
</table>
<?php
$pRows = ContestRound::forUser($uid);
$cnt = count($pRows);
if ($cnt) {
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>比赛名</th>
    <th>参加时间</th>
  </tr>
<?php
	$last = $j =0;
	foreach ($pRows as $i => $d) {
		if ($last==$d['cbid']) continue;
		$last=$d['cbid'];
        $j++;
?>
    <tr>
    <td><b><?=h($d['cname'])?></b></td>
    <td><a href="../contest/problem.php?ctid=<?=$d['ctid']?>" target="_blank"><?php echo date("Y-m-d",$d['subtime'])?></a></td>
    </tr>
<?php
	}
?>
</table>
<?php
}
?>
</div>
<div class='span8'>
<a href="../submit/index.php?uid=<?=$uid?>" target="_blank">查看全部提交记录</a>
<?php if(Privilege::allows('查看用户') || $_SESSION['ID']==$uid) { ?>
<a href="export.php?uid=<?=$uid?>" target="_blank" class='btn pull-right'>导出全部提交记录</a>
<?php  } ?>
<?php
// 一题可能提交多次：通过数按题目去重，提交次数则统计每一份当前可见的记录。
$accnt=0;
$pRows = Submission::history($uid);
$cnt = count($pRows);
if ($cnt) {
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php
$ppp=array();$j=0;
foreach ($pRows as $i => $d) {
    if($ppp[$d['pid']]) continue;
    if($d['accepted']) $accnt++;
    $ppp[$d['pid']] = true;
    if($j % 4 == 0) echo "<tr>";
    $j++;
?>
<td>
<a href='../submit/code.php?id=<?=$d['sid']?>' target='_blank'>
<?=$STR['lng'][$d['lang']]?>
<i class='icon-<?=($d['accepted']?"ok":"remove")?>'></i>
</a>
<a href="../problem/problem.php?pid=<?=$d['pid'] ?>" target="_blank">
<?=shortname($d['probname']) ?>
</a>
</td>
<?php
	}
if($j%4) for($i=$j%4; $i<4; $i++) echo "<td>";
?>
</table>
<?php
}
?>
<p class='alert'>
通过了<b><?=$accnt ?></b>道题，一共提交了<b><?=$cnt ?></b>次，通过率为<b><?php printf("%.2lf",$cnt==0?0:$accnt / $cnt * 100) ?>%</b>。
</p>
</div>
</div>
<?php
include_once("../include/footer.php");
?>
