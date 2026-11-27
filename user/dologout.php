<?php
// 退出入口从导航提交 POST；GET 打开确认表单。注销当前会话并清除浏览器保存的登录凭据。
require_once(__DIR__.'/../include/header.php');

requirePost();
Login::forget(); // 清除数据库中对应的长期登录凭据。
// 同时清掉旧版站点曾设置的身份 Cookie。
setcookie("cogs_usr","",-100,"/");
setcookie("cogs_pwd_hash","",-100,"/");
$_SESSION = []; // 清空当前请求中的身份，销毁持久化会话后旧标识也无法恢复。
if (!session_destroy()) i异常('退出（注销用户会话）失败！'); // 销毁失败时保留失败提示，避免误报注销成功。
i提示('退出（注销用户会话）成功！');
