<?php
// 只从表单接收场次、题号、语言和源文件；交卷人来自登录身份，截止时间从锁定的场次读取。
// 例：从 contest/problem.php?ctid=1&pid=1 选择 C++ 并提交源文件，表单将文件和会话令牌 POST 到这里。
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', ''); // 交卷人从已登录会话中取得。
requirePost(); // 上传必须附上比赛题面生成的表单令牌。
$id = ContestEntry::submitSource((int)($_POST['ctid'] ?? 0), (int)($_POST['pid'] ?? 0),
    (int)$_SESSION['ID'], $_POST['lang'] ?? '', $_FILES['file'] ?? [], $SET['dir_competition']);
if ($id === null) throw new DomainException('本场比赛当前不接受这次提交。', 403);
i提示('比赛提交代码成功！', 取路径('contest/code.php?csid='.$id));
