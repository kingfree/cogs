<?php
// GET /submit/index.php?pid=42 显示可读题目的提交。筛选和分页之前先应用题目阅读权限。
require_once("../include/header.php");
$mySubmissions = Submission::bestByProblem($_SESSION['ID']);
gethead(1,"","记录列表", (int)($_GET['uid'] ?? 0));

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
$limit = empty($_GET['show']) && empty($_GET['pid']) ? max(1, intdiv($SET['style_pagesize'], 2)) : null;
$submissions = Submission::search(($_GET['display'] ?? '') === 'ac', $_GET['uid'] ?? 0, $_GET['pid'] ?? 0, $limit);
$cnt = count($submissions);
$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
if(!$_GET['page']) {
    $_GET['page']=1;
    $st=0;
} else {
    if ($_GET['page']<1 || $_GET['page']>$totalpage)
        异常("页面错误！");
    else
        $st=(($_GET['page']-1)*$SET['style_pagesize']);
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
<?php if (!$err)
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=($submissions[$i] ?? false);
?>
<tr>
<td><a href='code.php?id=<?=$d['sid']?>'><?=$d['sid']?></a></td>
<td><?php if(!$_GET['pid']) {
    是否通过($mySubmissions[$d['pid']] ?? null);
    echo "<a href='?pid={$d['pid']}&uid=".(int)($_GET['uid'] ?? 0)."'>".shortname($d['probname'])."</a>";
    echo "<a href='../problem/problem.php?pid={$d['pid']}' target='_blank'><span class='icon-share'></span></a>";
} else
    echo "<a href='../problem/problem.php?pid={$d['pid']}' target='_blank'>".shortname($d['probname'])."</a>";
?></td>
<td><a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'><?=gravatar::showImage($d['email']);?></a>
<?php echo "<a href='?uid={$d['uid']}&pid=".(int)($_GET['pid'] ?? 0)."'>";
if(Privilege::allows("查看用户")) echo h($d['realname']); else echo h($d['nickname']);
echo "</a>"; ?></td>
<td class='wrap'><a href='code.php?id=<?=$d['sid']?>'><?=评测结果($d['result'], 20)?></a></td>
<td><span class="<?=$d['accepted']?'ok':'no'?>"><?=$d['score'] ?></span></td>
<td><a href='code.php?id=<?=$d['sid']?>'><?=$STR['lang'][$d['lang']]?></a></td>
<td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
<td><?php printf("%.2f",$d['memory']/1024) ?> MB </td>
<td><?php echo date('Y-m-d H:i:s',$d['subtime']); ?></td>
</tr>
<?php
}
?>
</table>
<?php
if($_GET['show'] || $_GET['pid'])
    分页($cnt, ($_GET['page'] ?? 1), '?pid='.$_GET['pid'].'&uid='.$_GET['uid'].'&display='.$_GET['display'].'&show='.$_GET['show'].'&');
?>
</div>
<?php
include_once("../include/footer.php");
?>
