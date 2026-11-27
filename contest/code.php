<?php
// contest/code.php?csid=1 只向本场读者开放。选手可核对自己的交卷，其他人的代码按成绩公布规则开放。
require_once(__DIR__.'/../include/header.php');
gethead(0, '普通用户', ''); // 先校验登录，再判断指定交卷的读者范围。
$entry = ContestEntry::forReader((int)($_GET['csid'] ?? 0), $SET['dir_competition']); // 只有可见的交卷才会连同其源码返回。
if (!$entry) throw new DomainException('提交记录不存在或未开放。', 404);
$round = $entry['round']; // 分数、逐点结果和资源用量共享同一个公布状态。
gethead(1,"sess","比赛代码", $entry['uid']);
require_once __DIR__.'/../include/hlighter.inc.php';

?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
    <th width="60px">比赛</th>
    <td><b><?=h($round['cname'])?></b></td>
    <th width="60px">评测结果</th>
    <td class='wrap'><?php if ($round['scoresVisible']) 评测结果($entry['result'], 100); else echo '未公布'; ?></td>
</tr>
<tr>
    <th>题目名称</th>
    <td><a href="problem.php?ctid=<?=$entry['ctid']?>&pid=<?php echo $entry['pid']; ?>"><?=h($entry['probname'])?></a></td>
    <th>最终得分</th>
    <td><?=$round['scoresVisible'] ? $entry['score'] : '未公布'?></td>
</tr>
<tr>
    <th>用户昵称</th>
    <td><a href="../user/detail.php?uid=<?php echo $entry['uid']; ?>" target="_blank"><?=h($entry['nickname'])?></a></td>
    <th>运行时间</th>
    <td><?php if ($round['scoresVisible']) printf("%.3f",$entry['runtime']/1000.0); else echo "—" ?> s </td>
</tr>
<tr>
    <th>代码语言</th>
    <td><?=$STR['lang'][$entry['lang']]?></td>
    <th>内存使用</th>
    <td><?php if ($round['scoresVisible']) printf("%.2f",$entry['memory']/1024); else echo "—" ?> MiB </td>
</tr>
<tr>
    <th>提交时间</th>
    <td colspan='3'><?php echo date('Y-m-d H:i:s',$entry['subtime']); ?></td>
</tr>
<tr>
</tr>
</table>
<?php $langstr = [0=>'pascal', 1=>'c', 2=>'cpp'][$entry['lang']] ?? ''; ?>
<a href="#code" class="btn btn-mini pull-right" data-toggle="modal">显示代码纯文本</a>
<div id="code" class="modal hide">
<button class='close' data-dismiss='modal'>×</button>
<div class='modal-body'>
<pre><?=h($entry['code'])?></pre>
</div>
</div>
<pre class="prettyprint linenums lang-<?=$langstr?>"><?=h($entry['code'])?></pre>
</div>
<?php
	include_once(__DIR__.'/../include/footer.php');
?>
