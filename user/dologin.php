<?php
// user/login.php 提交凭据。账号和密码验证通过才建立会话，savepwd 决定是否再发长期登录令牌。
require_once __DIR__.'/../include/header.php';
requirePost(); // GET 只出确认框，提交凭据时需要当前会话的表单令牌。
$user = Login::authenticate($_POST['username'] ?? '', $_POST['password'] ?? ''); // 同时支持现行密码与仍未升级的历史密码。
if (!$user || !Login::start($user)) i异常('用户名或密码错误。', 取路径('user/login.php'));
if (!empty($_POST['savepwd'])) Login::saveToken($user); // 只有勾选保存的设备才保留长期凭据。
i提示('登录成功！', 取路径('user/panel.php'));
