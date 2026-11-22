<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '管理用户', '');
requirePost();
$files = Submission::removeOrphans($SET['dir_source']);
gethead(1, '管理用户', '清除记录');
echo '<pre>'.h(implode("\n", $files)).'</pre>';
require dirname(__DIR__).'/include/footer.php';
