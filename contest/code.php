<?php
// contest/code.php?csid=1 只向本场读者开放。选手可核对自己的交卷，其他人的代码按成绩公布规则开放。
require_once("../include/header.php");
gethead(0, '普通用户', '');
$d = ContestEntry::withAuthor((int)($_GET['csid'] ?? 0))[0] ?? null;
$round = $d ? ContestRound::forReader((int)$d['ctid']) : null;
if (!$round || !isset($round['problems'][$d['pid']]) ||
    ((int)$d['uid'] !== (int)$_SESSION['ID'] && !$round['otherEntriesVisible'])) {
    http_response_code(404); exit('提交记录不存在或未开放。');
}
$ext = Language::TYPES[$d['lang']][0] ?? '';
$file = rtrim($SET['dir_competition'], '/').'/'.(int)$d['ctid'].'/'.(int)$d['uid'].'/'.basename($d['filename']).'.'.$ext;
$code = $ext !== '' && $ext !== 'zip' && is_file($file) && !is_link($file) ? file_get_contents($file) : '';
if (!mb_check_encoding($code, 'UTF-8')) $code = mb_convert_encoding($code, 'UTF-8', 'GBK');
gethead(1,"sess","比赛代码", $d['uid']);
require_once __DIR__.'/../include/hlighter.inc.php';

?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
    <th width="60px">比赛</th>
    <td><b><?=h($round['cname'])?></b></td>
    <th width="60px">评测结果</th>
    <td class='wrap'><?php if ($round['scoresVisible']) 评测结果($d['result'], 100); else echo '未公布'; ?></td>
</tr>
<tr>
    <th>题目名称</th>
    <td><a href="problem.php?ctid=<?=$d['ctid']?>&pid=<?php echo $d['pid']; ?>"><?=h($d['probname'])?></a></td>
    <th>最终得分</th>
    <td><?=$round['scoresVisible'] ? $d['score'] : '未公布'?></td>
</tr>
<tr>
    <th>用户昵称</th>
    <td><a href="../user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?=h($d['nickname'])?></a></td>
    <th>运行时间</th>
    <td><?php if ($round['scoresVisible']) printf("%.3f",$d['runtime']/1000.0); else echo "—" ?> s </td>
</tr>
<tr>
    <th>代码语言</th>
    <td><?=$STR['lang'][$d['lang']]?></td>
    <th>内存使用</th>
    <td><?php if ($round['scoresVisible']) printf("%.2f",$d['memory']/1024); else echo "—" ?> MiB </td>
</tr>
<tr>
    <th>提交时间</th>
    <td colspan='3'><?php echo date('Y-m-d H:i:s',$d['subtime']); ?></td>
</tr>
<tr>
</tr>
</table>
<?php     if($d['lang']==0) $langstr="pascal";
    else if($d['lang']==1) $langstr="c";
    else if($d['lang']==2) $langstr="cpp";
?>
<a href="#code" class="btn btn-mini pull-right" data-toggle="modal">显示代码纯文本</a>
<div id="code" class="modal hide">
<button class='close' data-dismiss='modal'>×</button>
<div class='modal-body'>
<pre><?=htmlspecialchars($code)?></pre>
</div>
</div>
<pre class="prettyprint linenums lang-<?=$langstr?>"><?=htmlspecialchars($code)?></pre>
</div>
<?php
	include_once("../include/footer.php");
?>
