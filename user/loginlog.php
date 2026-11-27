<?php
// 登录后通过 user/loginlog.php?uid=2 查该账号的记录；查看别人记录需要“查看用户”权限。
require_once(__DIR__.'/../include/header.php');
$userId = (int)($_GET['uid'] ?? 0); // 查看全站记录时 UID 为 0，必须具备查看用户权限。
gethead(1, '普通用户', '登录日志', $userId);
if ($userId !== (int)$_SESSION['ID'] && !Privilege::allows('查看用户')) { // 个人登录记录含 IP 和 UA，普通成员仅可读本人记录。
    异常('不是本人并且没有权限查看！', 取路径("user/detail.php?uid=$userId"));
}
$page = (int)($_GET['page'] ?? 1);
$logins = Login::history($userId); // 保持账号筛选贯穿查询和分页链接。
$total = count($logins);
$offset = 检测页面($total, $page); // 根据筛选后的数量计算本页起点。
?>

<form action="" method="get" class='form-inline center'>
检索： 用户UID
<input name="uid" type="number" value="<?=h($_GET['uid'] ?? '')?>" class='span1' />
<button type="submit" class='btn btn-primary'>检索</button>
</form>
<div class='row-fluid'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th width=40px>编号</th>
<th width=60px>用户</th>
<th width=120px>IP</th>
<th>UA</th>
<th width=80px>版本</th>
<th width=180px>时间</th>
</tr></thead>
<?php
foreach (array_slice($logins, $offset, (int)$SET['style_pagesize']) as $login) {
?>
<tr>
<td><?=$login['lid']?></td>
<td><a href="?uid=<?=$login['uid']?>"><?=gravatar::showImage($login['email']);?><?=h($login['realname'])?></a></td>
<td><?=h($login['ip'])?></td>
<td class="wrap"><?=h($login['ua'])?></td>
<td><?=h($login['version'])?></td>
<td><?=$login['ltime']?></td>
</tr>
<?php  } ?>
</table>
</div>
<?php
分页($total, $page, "?uid=$userId&");
include_once(__DIR__.'/../include/footer.php');
?>
