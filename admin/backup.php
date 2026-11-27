<?php
// 超级用户访问 admin/backup.php 选择备份内容；四类备份的名称、扩展名和数据来源见 Backup。
require_once __DIR__.'/../include/header.php';
gethead(1, '超级用户', '备份与恢复');  // 核验超级用户身份，再展示会暴露存储目录的表单。
$buttonStyles = ['database'=>'primary', 'usercode'=>'warning', 'compcode'=>'warning', 'testdata'=>'danger'];  // 颜色仅用于区分按钮，提交的种类来自 Backup。
?>
<div class="page">
<form method="post" action="dobackup.php" class="form-inline">
    <?=formTokenField() /* 查看列表也沿用同一后台 POST 入口的会话令牌。 */ ?>
    <input type="hidden" name="backtype" value="showback" />
    <button type="submit" class="btn btn-success">查看已备份的数据</button>
</form>
<hr />
<?php foreach (Backup::TYPES as $type => $kind) { /* 每个备份类别生成独立表单；种类、显示名和后缀取自同一处。 */ ?>
<form method="post" action="dobackup.php" class="form-inline">
    <?=formTokenField() /* 每个创建操作都随表单发送当前会话令牌。 */ ?>
    <h4><?=h($kind['label'])?> (<?=h($type)?>)</h4>
    <input type="hidden" name="backtype" value="<?=h($type)?>" />
    <div class="input-prepend input-append">
        <span class="add-on"><?=h(rtrim($SET['dir_databackup'], '/').'/'.$type.'/')?></span>
        <input type="text" name="filename" value="<?=date('Ymd') /* 默认标签仅为当天日期，发布时间和随机尾缀由后台追加。 */ ?>" />
        <span class="add-on">_&lt;时间&gt;_&lt;编号&gt;<?=h($kind['suffix'])?></span>
    </div>
    <button type="submit" class="btn btn-<?=$buttonStyles[$type] ?? 'primary'?> pull-right">备份<?=h($kind['label'])?></button>
</form>
<?php } ?>
</div>
<?php require __DIR__.'/../include/footer.php'; ?>
