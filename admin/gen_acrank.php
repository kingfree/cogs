<?php
// 管理员从后台 POST 到本页为现有账号重算积分。计分规则在 Ranking，页面按账号即时显示进度。
require_once __DIR__.'/../include/header.php';
gethead(1, '生成等级', '生成等级');  // 计算全站积分需要独立的“生成等级”权限。
requirePost();  // 后台菜单通过 GET 打开确认页，POST 后才写回积分。
?>
<div class="page">
开始计算……<br />
<?php
foreach (Account::directory() as $account) {  // 遍历现有账号，逐人完成计算和写回。
    [$attempts, $accepted, $grade] = Ranking::recalculate((int)$account['uid']);  // 取练习提交次数、通过题数，以及含比赛成绩的积分。
    if ($grade > 0) {  // 零分账号仍参与重算，页面只展开正分账号的明细。
        echo h($account['realname']), ' = <span class="ok">', $grade, '</span> ',
            '(<span class="no">', $accepted, '</span>/<span class="did">', $attempts, '</span>). ';  // 在积分旁列出通过题数与练习提交次数，便于管理员核对。
    }
    flush();  // 逐账号提交已有输出，让较长的重算能显示进度。
}
?>
<span class="ok">计算完成！</span>
</div>
<?php require __DIR__.'/../include/footer.php'; ?>
