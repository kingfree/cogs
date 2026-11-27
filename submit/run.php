<?php
/**
 * 普通交题和管理员重判入口。题目页 POST pid、lang 和 file；重判 POST pid、sid 和 rejudge。
 * 例如重判 SID 7 时，只用表单选中该记录，语言、原作者和源文件均由数据库确定。
 */
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();   // 提交、重判和试跑都占用评测资源，只接收带令牌的表单。

$problemId = (int)($_POST['pid'] ?? 0);
$problem = Problem::forReader($problemId);                         // 按当前读者的小组和权限取题。
if (!$problem) { http_response_code(404); exit('题目不存在或未开放。'); }   // 不向未获准参加的选手披露题目是否存在。
if ($problem['datacnt'] < 1 || $problem['datacnt'] > GradingLimits::CASES) {
    http_response_code(400);   // 无合法的测试点时不派发远端任务。
    exit('题目测试点数量无效。');
}

$owner = (int)$_SESSION['ID'];
$language = Language::id($_POST['lang'] ?? '');   // 普通交题用扩展名选编译器，重判改用旧记录。
$previous = null;   // 普通新提交没有历史 SID，只有获准重判时才读旧文件。
if (!empty($_POST['rejudge'])) {                                  // 重判跳过新文件上传，但提交人必须先有重判资格。
    if (!Privilege::allows('管理评测')) { http_response_code(403); exit; }   // 普通选手不能触发他人的重判。
    $previous = Submission::forRejudge((int)($_POST['sid'] ?? 0), $problemId);  // 先确认提交确实属于当前这道题。
    if (!$previous) { http_response_code(400); exit('提交记录与题目不符。'); }   // 旧记录与题号对应后才接管原作者和语言。
    $owner = (int)$previous['uid'];                                       // 重判成绩仍记在原作者名下。
    $language = (int)$previous['lang'];   // 防止重判时误用浏览器选择的另一种语言。
}
if ($language === null || !isset(Language::TYPES[$language]) || !TestData::name($problem['filename'])) {
    http_response_code(400);
    exit('提交语言或文件名无效。');   // 页面拒绝数据库里已失效的语言或不适合作文件名的题名。
}
$testOnly = ($_POST['testmode'] ?? '') === '1' && (Privilege::allows('测试题目') || (int)$problem['addid'] === (int)$_SESSION['ID']);   // 出题人或有测试权限者可试跑而不计分。
$job = new Judging($problem, $owner, $language, $SET['dir_source'], $previous['sid'] ?? null, $testOnly);
gethead(1, '普通用户', '评测');
require_once __DIR__.'/../include/hlighter.inc.php';
?>
<div class="row-fluid"><div class="span6">
<div class="alert alert-info">正在连接评测机...
<?php
flush();                                                               // 长任务开始前先让选手看到进度。
if (!$job->acquire((int)($_POST['judger'] ?? 0))) {
    异常('当前没有空闲的评测机，请稍后重新提交。', 取路径("problem/problem.php?pid=$problemId"));
}
?>
<span class="badge badge-info"><?=(int)$job->machine['grid']?></span>
<span class="label label-info"><?=h($job->machine['name'] ?? '')?> <?=h($job->machine['ver'] ?? '')?></span>
<?=h($job->machine['memo'])?>
</div>
<div class="alert alert-success">正在编译...
<?php
$pointTableOpen = false;                                               // 中途断线时，先关闭已输出的进度表再显示错误。
try {
    $job->prepareSource();                                              // 在原作者的目录中定位本次源码。
    if ($previous) $job->useSource($previous['srcname']);                // 重判复用历史文件，忽略表单附件。
    elseif (!$job->upload($_FILES['file'] ?? [])) {
        throw new DomainException('源代码上传失败。请检查文件大小。');   // 上传失败不进入编译阶段。
    }
    flush();
    if (!$job->compile()) {
?>
</div></div></div>
<div class="alert"><p>编译失败：</p><pre class="prettyprint"><?=h($job->compileMessage)?></pre></div>
<?php
    } else {
        $pointTableOpen = true;
?>
开始运行
<table class="table table-condensed"><tr><th>点</th><th>结果</th><th>得分</th><th>运行时间</th><th>内存使用</th><th>返回</th></tr>
<?php
        for ($point = 1; $point <= $problem['datacnt']; $point++) {
            $verdict = $job->run($point);                               // 单点成功后才进入整题统计。
            $result = $job->pointResult($point);                        // 此行和最终保存的成绩来自同一份结果。
?>
<tr><td><?=$point?></td><td><?=评测信息a($verdict)?></td><td><?=$result['score']?></td>
<td><?=sprintf('%.3f s', $result['time']/1000)?></td><td><?=$result['memory']?> KB</td><td><?=$result['exitCode']?></td></tr>
<?php
            flush();
        }
        echo '</table>';
        $pointTableOpen = false;
        $submissionId = $testOnly ? null : $job->savePractice();         // 试跑不入库；正式提交取得已保存的 SID。
        $showFailure = $job->failedPoint && ($owner === (int)$_SESSION['ID'] || Privilege::allows('查看数据'));
        $mayDownload = Privilege::allows('查看数据') || ($_SESSION['failed_cases'][$problem['filename']] ?? null) === $job->failedPoint;
?>
</div></div>
<div class="span6"><div class="alert">
<p>运行时间 <?=sprintf('%.3f', $job->totalTime()/1000)?> s</p>
<p>平均内存 <?=sprintf('%.2f', $job->averageMemory()/1024)?> MB</p>
<p>测试点通过状况 <?php if ($submissionId !== null) { ?><a href="code.php?id=<?=$submissionId?>"><?php } ?>
<?=评测结果($job->verdicts(), 30)?><?php if ($submissionId !== null) { ?></a><?php } ?></p>
<p>得分：<b><?=$job->score()?></b></p>
<p><a href="../problem/problem.php?pid=<?=$problemId?>">返回原题 “<?=h($problem['probname'])?>”</a></p>
<?php if ($job->accepted()) { ?>
<p class="ok">祝贺你通过了全部测试点！</p>
<p>顺便<a href="../problem/problem.php?pid=<?=$problemId?>">返回</a>为该题添加一个标签吧！</p>
<p>...或者看看<a href="../problem/comments.php?pid=<?=$problemId?>">其他人的评论</a>，<a href="../problem/comment.php?pid=<?=$problemId?>">发表</a>一下你的感想吧！</p>
<?php } else { ?>
<p class="no">你没有通过这道题！</p>
<p><a href="../docs/" target="_blank">为什么程序在我的电脑上能够正常运行，而在评测机上不能?</a></p>
<?php } ?>
<?php if ($showFailure && $mayDownload) { ?>
<form action="testdata.php" method="post" class="form-inline"><?=formTokenField()?>
<span class="badge">第<?=$job->failedPoint?>个</span>测试点数据：
<input name="filename" type="hidden" value="<?=h($problem['filename'])?>" />
<input name="point" type="hidden" value="<?=$job->failedPoint?>" />
<input name="io" type="submit" value="in" class="btn btn-success"/>
<input name="io" type="submit" value="ans" class="btn btn-success"/>
</form>
<?php } ?>
<?php if ($testOnly) { ?><p class="no">没有写入数据库</p><?php } ?>
</div></div></div>
<?php if ($showFailure) { ?>
<div class="alert"><p class="no">你在<span class="badge">第<?=$job->failedPoint?>个</span>测试点出现了爆零的情况，下面是该题的输入数据：</p>
<pre class="prettyprint"><?=h(gettexts($job->failedInput, 512))?></pre>
<p>标准答案与本次输出的开头如下：</p><pre class="prettyprint"><?=h(gettexts($job->difference, 512))?></pre>
<p><a href="../problem/problem.php?pid=<?=$problemId?>">返回原题 “<?=h($problem['probname'])?>”</a></p></div>
<?php } ?>
<?php
    }
} catch (PDOException $error) {
    error_log($error->getMessage());                                     // 数据库异常只记站点日志，页面保留可读提示。
    if ($pointTableOpen) echo '</table>';
    echo '<p class="alert alert-error">无法保存本次成绩，请稍后重试。</p></div></div></div>';
} catch (DomainException | RuntimeException $error) {
    // 已经开始向浏览器输出进度，故障反馈放在相同的进度页；禁止将未跑完的题更新成零分。
    if (!headers_sent()) http_response_code($error instanceof DomainException ? 400 : 503);
    if ($pointTableOpen) echo '</table>';
    echo '<p class="alert alert-error">'.h($error->getMessage()).'</p></div></div></div>';
} finally {
    $job->unlock();                                                      // 包括编译失败和中途断线都归还租约。
}
?>
<script type="text/javascript">SyntaxHighlighter.all();</script>
<?php require __DIR__.'/../include/footer.php'; ?>
