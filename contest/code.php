<?php
require_once("../include/header.php");
$entries = ContestEntry::withAuthor((int)$_GET['csid']);
$cnt = count($entries);
if ($cnt) {
	$d=($entries[0] ?? false);
    if(!Privilege::allows("查看比赛") && (time() < $d['endtime'] && $_SESSION['ID'] != $d['uid']))
        异常("比赛未结束！", 取路径("contest/index.php"));
    $ext = Language::TYPES[$d['lang']][0] ?? '';
    $file = rtrim($SET['dir_competition'], '/').'/'.(int)$d['ctid'].'/'.(int)$d['uid'].'/'.basename($d['filename']).'.'.$ext;
    $code = $ext !== 'zip' && is_file($file) && !is_link($file) ? file_get_contents($file) : '';
    if (!mb_check_encoding($code, 'UTF-8')) $code = mb_convert_encoding($code, 'UTF-8', 'GBK');
} else 异常("提交记录不存在");
gethead(1,"sess","比赛代码", $d['uid']);
require_once __DIR__.'/../include/hlighter.inc.php';

?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
    <th width="60px">比赛</th>
    <td><b><?=h($d['cname'])?></b></td>
    <th width="60px">评测结果</th>
    <td class='wrap'><?php 评测结果($d['result'], 100) ?></td>
</tr>
<tr>
    <th>题目名称</th>
    <td><a href="problem.php?ctid=<?=$d['ctid']?>&pid=<?php echo $d['pid']; ?>"><?=h($d['probname'])?></a></td>
    <th>最终得分</th>
    <td><?php echo $d['score'] ?></td>
</tr>
<tr>
    <th>用户昵称</th>
    <td><a href="../user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?=h($d['nickname'])?></a></td>
    <th>运行时间</th>
    <td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
</tr>
<tr>
    <th>代码语言</th>
    <td><?=$STR['lang'][$d['lang']]?></td>
    <th>内存使用</th>
    <td><?php printf("%.2f",$d['memory']/1024) ?> MiB </td>
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
