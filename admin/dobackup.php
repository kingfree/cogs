<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '超级用户', '');
requirePost();
$type = $_POST['backtype'] ?? '';
try {
    $message = $type === 'showback' ? implode("\n", Backup::listing($SET['dir_databackup'])) :
        '备份已保存为 '.Backup::create($type, $_POST['filename'] ?? '', $SET, $cfg);
} catch (DomainException $error) { http_response_code(400); exit(h($error->getMessage())); }
gethead(1, '超级用户', '备份');
?>
<div class="container"><a href="backup.php">返回备份页面</a><pre><?=h($message)?></pre></div>
<?php require dirname(__DIR__).'/include/footer.php'; ?>
