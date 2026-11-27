<?php
// 用户目录，例如 user/index.php?gid=1。选择分组会同时列出其下级组成员，搜索和分页沿用这个分组范围。
require_once("../include/header.php");
gethead(1,"","用户列表");
?>
<?php

    if ($_GET['gid']=="")
        $_GET['gid']=0;
    $pRows = UserGroup::byId($_GET['gid']);
    $cnt = count($pRows);
    $d=($pRows[0] ?? false);
    
    $subgroup=UserGroup::descendants($_GET['gid']);
    


?>
<div class='btn-group pull-left'>
<a href="grouplist.php" class='btn btn-success pull-left'><i class='icon-th-large icon-white'></i>用户分组列表</a>
<button class='btn btn-success dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
<ul id="group_now" class='dropdown-menu span4'>
<li>当前分组</li>
<li><a href=""><span class='label'><?=h($d['gname'])?></span><?=nl2br(sp2n(htmlspecialchars($d['memo']))) ?></a></li>
<?php if ($d['parent']) {
$e = UserGroup::tree()[$d['parent']] ?? null;
?>
<li class='divider'></li>
<li>上级分组</li>
<li><a href="index.php?gid=<?=$e['gid'] ?>"><?=h($e['gname'])?></a></li>
<?php } ?>
<?php
if ($subgroup!=array()) {
?>
<li class='divider'></li>
<li>下级分组</li>
<li><?php
foreach($subgroup as $value) {
$e = UserGroup::tree()[$value] ?? null;
?>
<a href="index.php?gid=<?=$e['gid'] ?>"><?=h($e['gname'])?></a>
<?php
}
?></li>
<?php } ?>
</ul>
</div>
<?php
$pRows = Account::search(array_merge([$_GET['gid']], $subgroup), $_GET['key'] ?? '',
    $_GET['rank'] ?? 'grade', $_GET['order'] ?? 'desc');
$cnt = count($pRows);
$st=检测页面($cnt, ($_GET['page'] ?? 1));
// 只加载当前页用户的权限标记，避免展示每一行时再次查询。
$privileges = Privilege::forUsers(array_column(array_slice($pRows, $st, (int)$SET['style_pagesize']), 'uid'));
?>
<form method="get" action="" class='form-search center'>
  <input name="gid" type="hidden" value="<?=h($_GET['gid'] ?? '')?>" />
  <input name="order" type="hidden" value="<?=$_GET['order']=='asc'?'desc':'asc'?>" />
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=h($_GET['key'] ?? '')?>' placeholder='搜索用户'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
</div>
<div class='btn-group'>排序方法：
  <button name="rank" type="submit" value='uid' class='btn'>UID</button>
  <button name="rank" type="submit" value='nickname' class='btn'>昵称</button>
  <?php if(Privilege::allows('查看用户')) { ?>
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
    <?php  if(Privilege::allows('查看用户')) { ?>
    <th class='admin' style="min-width: 4em;">姓名</th>
    <th style="min-width: 6ex;">阅读</th>
    <?php  } ?>
    <th>权限</th>
    <th>分组</th>
    <th style="min-width: 4ex;">通过</th>
    <th style="min-width: 6ex;">通过率</th>
    <th style="min-width: 6ex;">等级</th>
    <?php  if(Privilege::allows('查看用户')) { ?><th class='admin' style="min-width: 8em;">IP</th><?php  } ?>
    <?php  if(Privilege::allows('修改用户')) { ?><th class='admin' style="min-width: 4em;">操作</th><?php  } ?>
  </tr>
</thead>
<?php
    if (!$err) for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
        $d=($pRows[$i] ?? false);
?>
  <tr>
    <td><i><?=$i+1?></i></td>
    <td><?=$d['uid'] ?></td>
    <td><a href="detail.php?uid=<?=$d['uid'] ?>" title="<?=(sp2n(htmlspecialchars($d['memo'])))?>"><?=gravatar::showImage($d['email']);?><?=h($d['nickname'])?></a></td>
    <?php  if(Privilege::allows('查看用户')) { ?>
    <td>
        <?=h($d['realname'])?></td>
    <td>
        <a href="dodelimg.php?email=<?=md5($d['email'])?>" title="重建头像缓存"><i class="icon icon-picture"></i></a>
    <?=$d['readforce'] ?>
    </td>
    <?php  } ?>
    <td><?php
    $rRows = $privileges[$d['uid']] ?? [];
	$cnt1 = count($rRows);
	foreach ($rRows as $i1 => $e) {
        echo array_search($e['pri'],$pri) . " ";
    } ?></td>
    <td ><?="<a href='?gid={$d['gbelong']}'>{$d['gname']}</a>"; ?></td>
    <td ><?=$d['accepted'] ?></td>
    <td ><?=($d['submited'] ? round($d['accepted']/$d['submited']*100,1) : 0); ?>%</td>
    <td ><?=$d['grade'] ?></td>
    <?php  if(Privilege::allows('查看用户')) { ?>
    <td>
    <a href="loginlog.php?uid=<?=$d['uid'] ?>"><?=$d['lastip'] ?></a>
    </td><?php  } ?>
    <?php  if(Privilege::allows('修改用户')) { ?><td>
    <a href="edituser.php?uid=<?=$d['uid'] ?>" title="修改用户信息"><i class="icon icon-wrench"></i></a>
    <?php  if(Privilege::allows('修改权限')) { ?>
    <a href='privilege.php?way=edit&uid=<?=$d['uid']?>' title="赋予权限"><i class="icon icon-book"></i></a>
    <?php  } ?>
    <a href="doedituser.php?uid=<?=$d['uid'] ?>&action=del" onclick="return okdel('<?=h($d['nickname'])?>')" title="删除用户账号"><i class="icon icon-remove"></i></a>
    </td><?php  } ?>
  </tr>
<?php
    }
?>
</table>
<?php  分页($cnt, ($_GET['page'] ?? 1), '?key='.$_GET['key'].'&sc'.$_GET['sc'].'&rank='.$_GET['rank'].'&order='.$_GET['order'].'&caid='.$_GET['caid'].'&'); ?>
</div>
<?php
include_once("../include/footer.php");
?>
