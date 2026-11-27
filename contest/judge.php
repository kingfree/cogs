<?php
// 从 comptime.php POST 选定交卷编号；页面依次调用 compile.php，将每份评测结果更新到对应行。
require_once(__DIR__.'/../include/header.php');
gethead(0, '查看比赛', ''); // 在加载管理表单前核对访问权限。
requirePost(); // 批量评测只能由带令牌的场次管理表单发起。
$action = $_POST['do'] ?? ''; // 页面支持评测勾选交卷或整个场次。
if (!in_array($action, ['评测选定','评测全部'], true)) throw new DomainException('未知的评测操作。', 400);
$selection = $_POST[$action === '评测选定' ? 'doit' : 'doall'] ?? []; // 表单中两类选择各有自己的编号列表。
$entries = ContestEntry::judgingQueue(is_array($selection) ? $selection : []); // 不存在的交卷不会进入前端队列。
gethead(1, '', '比赛评测');
?>
<div class='container'>
<h2><?=h($action)?></h2>
<script>
var entryIds = <?=json_encode(array_map('intval', array_column($entries, 'csid')))?>; // 与下方表格共享交卷顺序。
var judgeId = <?=(int)($_POST['judger'] ?? 0)?>; // 0 表示自动选择评测机。
var token = <?=json_encode(formToken())?>;

function startJudging() {
    var position = 0; // 点击重新评测时，从第一份交卷开始。
    var button = document.getElementById("st");
    var bar = document.getElementById("progress"); // 进度条只统计已得到有效结果的交卷。
    button.disabled = true;
    button.value = "正在评测...";
    bar.style.width = "0%"; // 新一轮不能沿用上次的完成百分比。
    document.getElementById("progressd").className = "progress progress-striped progress-warning active";

    function gradeNext() {
        var entryId = entryIds[position]; // 上一份完成后才会派发下一份。
        var score = document.getElementById("score" + entryId);
        var verdict = document.getElementById("result" + entryId); // 用交卷编号定位该行的两个结果格。
        var request = new XMLHttpRequest();
        score.textContent = "正在评测...";
        document.getElementById("nowp").textContent = position + 1; // 参赛者看到的序号从 1 起。
        request.open("POST", "compile.php");
        request.setRequestHeader("X-COGS-Token", token); // 每份评测都会写库，需要携带表单令牌。
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        request.onload = function () {
            var fields = request.responseText.split("!"); // C 表示编译失败，其余结果可能包含时间和内存。
            if (request.status !== 200 || !/^\d+$/.test(fields[0]) || !fields[1]) {
                stopped(); // 机位忙或服务失败时留下当前行并开放重试。
                return;
            }
            score.textContent = fields[0]; // 响应字段只作为文本显示。
            verdict.textContent = fields[1];
            position++;
            bar.style.width = Math.round(position / entryIds.length * 100) + "%"; // 最后一份完成时到达 100%。
            if (position < entryIds.length) { gradeNext(); return; }
            button.disabled = false; // 完成本轮后允许主动重判。
            button.value = "重新评测";
            document.getElementById("progressd").className = "progress progress-striped progress-success";
        };
        function stopped() {
            score.textContent = "评测中断，请重试";
            button.disabled = false; // 网络断开也能重新启动整轮。
            button.value = "重新评测";
            document.getElementById("progressd").className = "progress progress-striped progress-danger";
        }
        request.onerror = stopped;
        request.send("csid=" + entryId + "&judger=" + judgeId); // 机位选择由整轮共用。
    }
    if (entryIds.length) gradeNext(); // 空选择时不会向服务器派发交卷。
}
</script>
<input type="button" id="st" value="开始评测" class="btn btn-primary" onclick="startJudging()" <?=$entries ? '' : 'disabled'?> />
<div id="progressd"><div id="progress" class="bar" style="width: 0%;"></div></div>
当前第<span id="nowp">0</span>个 共<?=count($entries)?>个
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>CSID</th>
    <th>真实姓名</th>
    <th>题目名</th>
    <th>文件名</th>
    <th>代码</th>
    <th>测试点</th>
    <th>得分</th>
  </tr>
<?php
    foreach ($entries as $entry) { // 此清单也决定批量执行的顺序。
?>
  <tr>
    <td><?=$entry['csid'] ?></td>
    <td><a href="../user/detail.php?uid=<?=$entry['uid'] ?>" target="_blank"><?=h($entry['realname'])?></a></td>
    <td><a href="problem.php?pid=<?=$entry['pid'] ?>&ctid=<?=$entry['ctid'] ?>" target="_blank"><?=shortname($entry['probname']) ?></a></td>
    <td><code><?=h($entry['filename'])?></code></td>
    <td><a href="code.php?csid=<?=$entry['csid'] ?>" target="_blank"><?=$STR['lang'][$entry['lang']] ?></a></td>
    <td id="result<?=$entry['csid'] ?>"><?=评测结果($entry['result'], 50) ?></td>
    <td id="score<?=$entry['csid'] ?>"><?=$entry['score'] ?></td>
  </tr>
<?php
    }
?>
</table>
</div>
<?php
    include_once(__DIR__.'/../include/footer.php');
?>
