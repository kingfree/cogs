<?php
// 管理页 POST 到 admin/clear_record.php 清理孤立源码。Submission 按提交记录和文件年龄筛选，页面列出实际移除的文件。
require_once __DIR__.'/../include/header.php';
gethead(0, '管理用户', '');
requirePost();
$files = Submission::removeOrphans($SET['dir_source']);
gethead(1, '管理用户', '清除记录');
echo '<pre>'.h(implode("\n", $files)).'</pre>';
require dirname(__DIR__).'/include/footer.php';
