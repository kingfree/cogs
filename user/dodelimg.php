<?php
// 控制面板的清理头像操作 POST 到此处，按当前登录邮箱删除本地各尺寸 Gravatar 缓存。
require_once __DIR__.'/../include/header.php';
gethead(8, '普通用户', '');
requirePost();
foreach (glob(dirname(__DIR__).'/images/gravatar/'.md5($_SESSION['email']).'s[0-9]*') ?: [] as $file) {
    if (is_file($file)) unlink($file);
}
提示('头像缓存已清除。', 取路径('user/panel.php'));
