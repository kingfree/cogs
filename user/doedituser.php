<?php
require_once("../include/header.php");
gethead(8,"修改用户","");
requirePost();
$target = (int)($_GET['uid'] ?? 0);
$grants = array_column(Privilege::forUser($target), 'pri');
if ($target === 1 || in_array(2, $grants)) {
    if (!Privilege::allows('超级用户') || ($_GET['action'] ?? '') === 'del') { http_response_code(403); exit('不能更改该管理账号。'); }
} elseif ($grants && !Privilege::allows('修改权限')) { http_response_code(403); exit('需要修改权限。'); }


if ($_GET['action']=="del") {
    $accounts = Account::role($_GET['uid']);
    $cnt = count($accounts);
    if(!$cnt) 异常("无此用户！",取路径("user/index.php"));
    $d=($accounts[0] ?? false);
    Account::remove($_GET['uid']);
    提示("删除用户成功！",取路径("user/index.php"));
} else if ($_GET['action']=="edit") {
    $ttt="";
    Account::administer($_POST['nickname'], $_POST['readforce'], $_POST['email'], $_POST['memo'], $_POST['realname'], $_POST['gbelong'], $_GET['uid']);
    if ($_POST['reset'] == "reset") {
        $temporary = bin2hex(random_bytes(12));
        Account::changePassword(Password::hash($temporary), $_GET['uid']);
        header('Cache-Control: no-store');
        $ttt=" 临时密码为 ".h($temporary);
    }
    提示("编辑用户成功！$ttt",取路径("user/detail.php?uid={$_GET['uid']}"));
}
?>
