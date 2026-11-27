<?php
// 用户目录，例如 user/index.php?gid=1。选择分组会同时列出其下级组成员，搜索和分页沿用这个分组范围。
require_once(__DIR__.'/../include/header.php');
gethead(1,"","用户列表");
$groupId = (int)($_GET['gid'] ?? 0); // 无 gid 时从全站用户目录开始。
$groups = UserGroup::tree();
// 0 是目录中的“全部用户”，数据库只保存真实分组。
$group = $groupId === 0 ? ['gname'=>'全部用户', 'memo'=>'', 'parent'=>0] : ($groups[$groupId] ?? null);
if (!$group) 异常('无此分组！', 取路径('user/grouplist.php'));
$children = UserGroup::descendants($groupId); // 选择一个分组会一并列出所有下属组成员。
$parent = $groups[$group['parent']] ?? null; // 有上级时提供导航，虚拟公开组与已断开的父链无需链接。
$keyword = $_GET['key'] ?? '';
$sort = $_GET['rank'] ?? 'grade'; // 没有手选排序时沿用积分榜顺序。
$order = $_GET['order'] ?? 'desc';
$page = (int)($_GET['page'] ?? 1);
$users = Account::search(array_merge([$groupId], $children), $keyword, $sort, $order); // 页面、排序、权限汇总共享同一组筛选结果。
$total = count($users);
$offset = 检测页面($total, $page); // 行号从全列表的起点算，不在每页重新从 1 开始。
// 同一页展示的姓名、IP 与操作由当前访问者的权限决定，权限列表只查询一次。
$pageUsers = array_slice($users, $offset, (int)$SET['style_pagesize']);
$privileges = Privilege::forUsers(array_column($pageUsers, 'uid')); // 只查这一页实际出现的用户编号。
$canViewUsers = Privilege::allows('查看用户');
$canEditUsers = Privilege::allows('修改用户'); // 修改资料与查看姓名是独立权限。
$canGrant = Privilege::allows('修改权限');
?>
<div class='btn-group pull-left'>
<a href="grouplist.php" class='btn btn-success pull-left'><i class='icon-th-large icon-white'></i>用户分组列表</a>
<button class='btn btn-success dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
<ul id="group_now" class='dropdown-menu span4'>
<li>当前分组</li>
<li><a href=""><span class='label'><?=h($group['gname'])?></span><?=nl2br(sp2n(h($group['memo']))) ?></a></li>
<?php if ($parent && $group['parent']) { ?>
<li class='divider'></li>
<li>上级分组</li>
<li><a href="index.php?gid=<?=$parent['gid'] ?>"><?=h($parent['gname'])?></a></li>
<?php } ?>
<?php
if ($children) {
?>
<li class='divider'></li>
<li>下级分组</li>
<li><?php
foreach ($children as $childId) {
$child = $groups[$childId];
?>
<a href="index.php?gid=<?=$child['gid'] ?>"><?=h($child['gname'])?></a>
<?php
}
?></li>
<?php } ?>
</ul>
</div>
<form method="get" action="" class='form-search center'>
  <input name="gid" type="hidden" value="<?=$groupId?>" />
  <input name="order" type="hidden" value="<?=$order === 'asc'?'desc':'asc'?>" />
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=h($keyword)?>' placeholder='搜索用户'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
</div>
<div class='btn-group'>排序方法：
  <button name="rank" type="submit" value='uid' class='btn'>UID</button>
  <button name="rank" type="submit" value='nickname' class='btn'>昵称</button>
  <?php if($canViewUsers) { ?>
  <button name="rank" type="submit" value='realname' class='btn'>姓名</button>
  <?php  } ?>
  <button name="rank" type="submit" value='readforce' class='btn'>阅读</button>
  <button name="rank" type="submit" value='gbelong' class='btn'>分组</button>
  <button name="rank" type="submit" value='accepted' class='btn'>通过</button>
  <button name="rank" type="submit" value='accepted/submited' class='btn'>通过率</button>
  <button name="rank" type="submit" value='grade' class='btn'>等级</button>
</div>
</form>
<script language=javascript>
function okdel(name) {
    if(confirm("你确定要删除 "+name+" 的所有信息吗？\n删除之后将不可恢复！"))
        return true;
    return false;
}
</script>
<div class='row-fluid'>
<table id="userlist" class='table table-striped table-condensed table-bordered fiexd'>
<thead>
  <tr>
    <th style="min-width: 3ex;"></th>
    <th style="min-width: 3ex;">UID</th>
    <th>昵称</th>
    <?php  if($canViewUsers) { ?>
    <th class='admin' style="min-width: 4em;">姓名</th>
    <th style="min-width: 6ex;">阅读</th>
    <?php  } ?>
    <th>权限</th>
    <th>分组</th>
    <th style="min-width: 4ex;">通过</th>
    <th style="min-width: 6ex;">通过率</th>
    <th style="min-width: 6ex;">等级</th>
    <?php  if($canViewUsers) { ?><th class='admin' style="min-width: 8em;">IP</th><?php  } ?>
    <?php  if($canEditUsers) { ?><th class='admin' style="min-width: 4em;">操作</th><?php  } ?>
  </tr>
</thead>
<?php
foreach ($pageUsers as $rowIndex => $user) {
?>
  <tr>
    <td><i><?=$offset + $rowIndex + 1?></i></td>
    <td><?=$user['uid'] ?></td>
    <td><a href="detail.php?uid=<?=$user['uid'] ?>" title="<?=(sp2n(htmlspecialchars($user['memo'])))?>"><?=gravatar::showImage($user['email']);?><?=h($user['nickname'])?></a></td>
    <?php  if($canViewUsers) { ?>
    <td>
        <?=h($user['realname'])?></td>
    <td>
        <a href="dodelimg.php?email=<?=md5($user['email'])?>" title="重建头像缓存"><i class="icon icon-picture"></i></a>
    <?=$user['readforce'] ?>
    </td>
    <?php  } ?>
    <td><?php
    foreach ($privileges[$user['uid']] ?? [] as $grant) {
        echo h(array_search($grant['pri'], $pri)).' ';
    } ?></td>
    <td ><a href="?gid=<?=$user['gbelong']?>"><?=h($user['gname'])?></a></td>
    <td ><?=$user['accepted'] ?></td>
    <td ><?=($user['submited'] ? round($user['accepted']/$user['submited']*100,1) : 0); ?>%</td>
    <td ><?=$user['grade'] ?></td>
    <?php  if($canViewUsers) { ?>
    <td>
    <a href="loginlog.php?uid=<?=$user['uid'] ?>"><?=h($user['lastip'])?></a>
    </td><?php  } ?>
    <?php  if($canEditUsers) { ?><td>
    <a href="edituser.php?uid=<?=$user['uid'] ?>" title="修改用户信息"><i class="icon icon-wrench"></i></a>
    <?php  if($canGrant) { ?>
    <a href='privilege.php?way=edit&uid=<?=$user['uid']?>' title="赋予权限"><i class="icon icon-book"></i></a>
    <?php  } ?>
    <a href="doedituser.php?uid=<?=$user['uid'] ?>&action=del" data-name="<?=h($user['nickname'])?>" onclick="return okdel(this.getAttribute('data-name'))" title="删除用户账号"><i class="icon icon-remove"></i></a>
    </td><?php  } ?>
  </tr>
<?php
    }
?>
</table>
<?php 分页($total, $page, '?'.http_build_query(['gid'=>$groupId, 'key'=>$keyword, 'rank'=>$sort, 'order'=>$order]).'&'); ?>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
