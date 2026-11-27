<?php
// GET /submit/code.php?id=42 显示可读的评测结果。省略 id 时显示当前读者能看的最新记录。
require_once __DIR__.'/../include/header.php';
$sid = (int)($_GET['id'] ?? 0);
$submission = Submission::forReader($sid ?: null);                   // 省略编号时获取本人能见的最新记录。
if (!$submission) { http_response_code(404); exit('提交记录不存在。'); }   // 无权读题与没有此记录共用同一响应。
$sid = (int)$submission['sid'];   // 默认最新记录也要用实际 SID 生成重判表单。
gethead(1,"","{$submission['probname']} - {$submission['nickname']} - {$sid}", $submission['uid']);
require_once __DIR__.'/../include/hlighter.inc.php';
?>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
    <th width="60px">记录编号</th>
    <td><?=$submission['sid']?></td>
    <th width="60px">评测结果</th>
    <td class='wrap' colspan='3'><?php 评测结果($submission['result'], 100) ?></td>
</tr>
<tr>
    <th>题目名称</th>
    <td><a href="../problem/problem.php?pid=<?php echo $submission['pid']; ?>" target="_blank"><?=h($submission['probname'])?></a></td>
    <th>最终得分</th>
    <td><?php echo $submission['score'] ?></td>
    <th rowspan='3' width="60px">重新评测</th>
    <td rowspan='3'>
<?php  if(Privilege::allows('管理评测')) { ?>
    <form method="post" action="../submit/run.php" class='form-inline'><?=formTokenField()?>
        <input name="pid" type="hidden" id="pid" value="<?=$submission['pid']; ?>" />
        <input name="sid" type="hidden" id="sid" value="<?=$submission['sid']; ?>" />
        <input type="hidden" name="rejudge" value="1">
        <input type="hidden" name="lang" value="<?php echo langnumtostr($submission['lang']) ?>">
    <select name='judger' id='judger' >
    <option value=0 selected=selected>自动选择</option>
<?php
    foreach (Judge::enabled() as $judge) {                               // 重判只能选择已启用的机位。
        echo "<option value={$judge['grid']} >".h($judge['memo'])."</option>";
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
    <td><a href="../user/detail.php?uid=<?php echo $submission['uid']; ?>" target="_blank"><?=h(Privilege::allows('查看用户') ? $submission['realname'] : $submission['nickname'])?></a></td>
    <th>是否通过</th>
    <td><?php echo $submission['accepted']?"<span class='ok'>通过":"<span class='no'>未通过"; ?></span></td>
</tr>
<tr>
    <th>代码语言</th>
    <td><?=$STR['lang'][$submission['lang']]?></td>
    <th>运行时间</th>
    <td><?php printf("%.3f",$submission['runtime']/1000.0) ?> s </td>
</tr>
<tr>
    <th>提交时间</th>
    <td><?php echo date('Y-m-d H:i:s',$submission['subtime']); ?></td>
    <th>内存使用</th>
    <td><?php printf("%.2f",$submission['memory']/1024) ?> MiB </td>
    <th>IP</th>
    <td><?php if(Privilege::allows('查看用户')) { echo h($submission['IP']);  } ?></td>
</tr>
<tr>
</tr>
</table>
<?php
if (!$submission['can_read_source']) {
    echo '<h1>您没有权限查看代码。</h1>';
} elseif ((int)$submission['lang'] !== 3) {                              // 答案包是二进制文件，没有源码文本视图。
    $code = Submission::sourceText($SET['dir_source'], $submission);      // 取得已获准阅读的代码，兼容旧 GBK 文件。
    $highlight = (int)$submission['lang'] === 0 ? 'pascal' : langnumtostr($submission['lang']);
?>
<a href="#code" class="btn btn-mini pull-right" data-toggle="modal">显示代码纯文本</a>
<div id="code" class="modal hide">
<button class='close' data-dismiss='modal'>×</button>
<div class='modal-body'>
<pre><?=htmlspecialchars($code)?></pre>
</div>
</div>
<pre class="prettyprint linenums lang-<?=$highlight?>"><?=htmlspecialchars($code)?></pre>
<?php } ?>
</div>
<?php
    include_once(__DIR__.'/../include/footer.php');
?>

