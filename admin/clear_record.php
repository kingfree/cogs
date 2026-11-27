<?php
// 管理页 POST 到 admin/clear_record.php 清理孤立源码。Submission 按提交记录和文件年龄筛选，页面列出实际移除的文件。
require_once __DIR__.'/../include/header.php';
gethead(0, '管理用户', '');  // 先核验管理权限，避免向无权限访问者显示确认表单。
requirePost();  // 旧菜单的 GET 链接停在确认页；确认 POST 才清理。
$files = Submission::removeOrphans($SET['dir_source']);  // 按源码目录和提交记录核对，只取实际删除的文件。
gethead(1, '管理用户', '清除记录');  // 清理完成后再输出结果页。
echo '<pre>'.h(implode("\n", $files)).'</pre>';  // 文件名按行列出，并按普通文本显示。
require dirname(__DIR__).'/include/footer.php';
