<?php
// 失败测试点下载入口。提交 filename、point 和 io（in 或 ans），由 TestData 校验数据权限和会话中的失败点。
// 例：测试失败后从结果页 POST filename=sum、point=1、io=in 和当前表单令牌，读取本站允许查看的首点输入。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
TestData::sendCase($_POST['filename'] ?? '', (int)($_POST['point'] ?? 0), $_POST['io'] ?? 'in');
