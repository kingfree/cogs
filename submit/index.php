<?php
// GET /submit/index.php?pid=42 显示可读题目的提交。筛选和分页之前先应用题目阅读权限。
require_once(__DIR__.'/../include/header.php');
$filterUser = (int)($_GET['uid'] ?? 0);                    // 点击选手名后只列出这位选手。
$filterProblem = (int)($_GET['pid'] ?? 0);                 // 从题面跳来时保留题号。
$onlyAccepted = ($_GET['display'] ?? '') === 'ac';         // 与“全部／通过”按钮共用同一筛选条件。
$paginated = !empty($_GET['show']) || $filterProblem;
$pageSize = max(1, (int)$SET['style_pagesize']);            // 站点设置决定每页显示几条记录。
$mySubmissions = Submission::bestByProblem($_SESSION['ID']);
gethead(1, "", "记录列表", $filterUser);

?>
<div class='row-fluid'>
<a class="btn btn-success pull-left" href="graderlist.php">评测机列表</a>
<form id="search_submit" method="get" class='form-search center'>
<span>
用户UID
<input name="uid" type="number" value="<?=h($_GET['uid'] ?? '')?>" class='span1' />
题目PID
<input name="pid" type="number" value="<?=h($_GET['pid'] ?? '')?>" class='span1' />
<input name="show" type="hidden" value="<?=h($_GET['show'] ?? '')?>" />
<input name="display" type="hidden" value="<?=h($_GET['display'] ?? '')?>" />
<button type="submit" class='btn btn-primary'>检索</button>
</span>
<span class='btn-group pull-right'>
<button name='display' value='all' class='btn'>全部</button>
<button name='display' value='ac' class='btn'>通过</button>
</span>
<button name='show' value='yes' class='btn btn-inverse'>显示分页记录</button>
</form>
<?php
$page = (int)($_GET['page'] ?? 1);                                      // 站点页码从 1 开始。
try {
    [$visiblePage, $total] = Submission::page($onlyAccepted, $filterUser, $filterProblem, $page, $pageSize, $paginated);
} catch (OutOfBoundsException $error) {
    异常($error->getMessage());                                           // 旧书签翻到末页以外时提示用户重新选择。
}
?>
<table id="submitlist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th style="min-width: 6ex;">SID</th>
<th>题目</th>
<th>用户</th>
<th style="min-width: 20ex;">结果</th>
<th style="min-width: 5ex;">得分</th>
<th style="min-width: 6ex;">语言</th>
<th style="min-width: 10ex;">用时</th>
<th style="min-width: 10ex;">内存</th>
<th style="min-width: 20ex;">时间</th>
</tr></thead>
<?php foreach ($visiblePage as $submission) { ?>
<tr>
<td><a href='code.php?id=<?=$submission['sid']?>'><?=$submission['sid']?></a></td>
<td><?php if (!$filterProblem) {
    是否通过($mySubmissions[$submission['pid']] ?? null);
    echo "<a href='?pid={$submission['pid']}&uid={$filterUser}'>".shortname($submission['probname'])."</a>";
    echo "<a href='../problem/problem.php?pid={$submission['pid']}' target='_blank'><span class='icon-share'></span></a>";
} else
    echo "<a href='../problem/problem.php?pid={$submission['pid']}' target='_blank'>".shortname($submission['probname'])."</a>";
?></td>
<td><a href='../user/detail.php?uid=<?=$submission['uid']?>' target='_blank'><?=gravatar::showImage($submission['email']);?></a>
<?php echo "<a href='?uid={$submission['uid']}&pid={$filterProblem}'>";
if(Privilege::allows("查看用户")) echo h($submission['realname']); else echo h($submission['nickname']);
echo "</a>"; ?></td>
<td class='wrap'><a href='code.php?id=<?=$submission['sid']?>'><?=评测结果($submission['result'], 20)?></a></td>
<td><span class="<?=$submission['accepted']?'ok':'no'?>"><?=$submission['score'] ?></span></td>
<td><a href='code.php?id=<?=$submission['sid']?>'><?=$STR['lang'][$submission['lang']]?></a></td>
<td><?php printf("%.3f",$submission['runtime']/1000.0) ?> s </td>
<td><?php printf("%.2f",$submission['memory']/1024) ?> MB </td>
<td><?php echo date('Y-m-d H:i:s',$submission['subtime']); ?></td>
</tr>
<?php
}
?>
</table>
<?php
if ($paginated) {
    $query = http_build_query(['pid'=>$filterProblem, 'uid'=>$filterUser, 'display'=>$onlyAccepted ? 'ac' : 'all', 'show'=>'yes']);
    分页($total, $page, '?'.$query.'&', $pageSize);                       // 翻页时沿用题号、用户和通过状态。
}
?>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
