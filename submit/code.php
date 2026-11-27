<?php
// GET /submit/code.php?id=42 显示可读的评测结果。省略 id 时显示当前读者能看的最新记录。
require_once __DIR__.'/../include/header.php';
$sid = (int)($_GET['id'] ?? 0);
$d = Submission::forReader($sid ?: null);
if (!$d) { http_response_code(404); exit('提交记录不存在。'); }
$sid = (int)$d['sid'];
gethead(1,"","{$d['probname']} - {$d['nickname']} - {$sid}", $d['uid']);
require_once __DIR__.'/../include/hlighter.inc.php';
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
    <th width="60px">记录编号</th>
    <td><?=$d['sid']?></td>
    <th width="60px">评测结果</th>
    <td class='wrap' colspan='3'><?php 评测结果($d['result'], 100) ?></td>
</tr>
<tr>
    <th>题目名称</th>
    <td><a href="../problem/problem.php?pid=<?php echo $d['pid']; ?>" target="_blank"><?=h($d['probname'])?></a></td>
    <th>最终得分</th>
    <td><?php echo $d['score'] ?></td>
    <th rowspan='3' width="60px">重新评测</th>
    <td rowspan='3'>
<?php  if(Privilege::allows('管理评测')) { ?>
    <form method="post" action="../submit/run.php" class='form-inline'><?=formTokenField()?>
        <input name="pid" type="hidden" id="pid" value="<?=$d['pid']; ?>" />
        <input name="sid" type="hidden" id="sid" value="<?=$d['sid']; ?>" />
        <input type="hidden" name="rejudge" value="1">
        <input type="hidden" name="lang" value="<?php echo langnumtostr($d['lang']) ?>">
    <select name='judger' id='judger' >
    <option value=0 selected=selected>自动选择</option>
<?php
    $judges = Judge::enabled();
    $cnt = count($judges);
    foreach ($judges as $i => $e) {
        echo "<option value={$e['grid']} >".h($e['memo'])."</option>";
    }
?>       
    </select>
    <button type="submit" class='btn'>重新评测</button>
    </form>
<?php  } ?>
    </td>
</tr>
<tr>
    <th>用户昵称</th>
    <td><a href="../user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?=h(Privilege::allows('查看用户') ? $d['realname'] : $d['nickname'])?></a></td>
    <th>是否通过</th>
    <td><?php echo $d['accepted']?"<span class='ok'>通过":"<span class='no'>未通过"; ?></span></td>
</tr>
<tr>
    <th>代码语言</th>
    <td><?=$STR['lang'][$d['lang']]?></td>
    <th>运行时间</th>
    <td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
</tr>
<tr>
    <th>提交时间</th>
    <td><?php echo date('Y-m-d H:i:s',$d['subtime']); ?></td>
    <th>内存使用</th>
    <td><?php printf("%.2f",$d['memory']/1024) ?> MiB </td>
    <th>IP</th>
    <td><?php if(Privilege::allows('查看用户')) { echo $d['IP'];  } ?></td>
</tr>
<tr>
</tr>
</table>
<?php
if ($d['can_read_source']) {
    $path = Submission::sourcePath($SET['dir_source'], (int)$d['uid'], $d['srcname']);
    $code = $path !== null && $d['lang'] != 3 ? file_get_contents($path) : '';
    if (!mb_check_encoding($code, 'UTF-8')) $code = mb_convert_encoding($code, 'UTF-8', 'GBK');
    if($d['lang']==0) $langstr="pascal";
    else if($d['lang']==1) $langstr="c";
    else if($d['lang']==2) $langstr="cpp";
    if($d['lang'] == 3) {
    } else {
?>
<a href="#code" class="btn btn-mini pull-right" data-toggle="modal">显示代码纯文本</a>
<div id="code" class="modal hide">
<button class='close' data-dismiss='modal'>×</button>
<div class='modal-body'>
<pre><?=htmlspecialchars($code)?></pre>
</div>
</div>
<pre class="prettyprint linenums lang-<?=$langstr?>"><?=htmlspecialchars($code)?></pre>
<?php } } else {
?>
    <h1>您没有权限查看代码。</h1>
<?php } ?>
</div>
<?php
    include_once("../include/footer.php");
?>

