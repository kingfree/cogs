<?php
// 在 user/privilege.php 查看账号授权；表单传 way=ins 或 del、uid 和 pri，分别授予或撤销对应权限。
require_once(__DIR__.'/../include/header.php');
gethead(1,"修改权限","权限列表");

$uid = (int)($_POST['uid'] ?? $_GET['uid'] ?? 0); // 用户目录的“编辑”链接会预填此编号。
$number = (int)($_POST['pri'] ?? $_GET['pri'] ?? 0); // 表单和旧链接沿用已存入数据库的数字编号。
$action = $_POST['way'] ?? $_GET['way'] ?? '';
$message = ''; // 首次打开与重复授予沿用无变更提示的界面。
if ($action === 'ins' || $action === 'del') {
    requirePost(); // 旧的删除链接在 GET 时先显示确认，再提交带令牌的 POST。
    $name = array_search($number, Privilege::NAMES, true); // 权限表以站点定义的名称为准，拒绝未知编号。
    if ($uid < 1 || $name === false) 异常('用户或权限编号无效。');
    if ($action === 'ins') {
        try {
            if (Privilege::grant($uid, $number)) $message = "用户 $uid 已添加权限 $name ！"; // 只在新授权确实落库时显示成功消息。
        } catch (InvalidArgumentException $error) {
            异常($error->getMessage());
        }
    } else {
        Privilege::revoke($uid, $number); // 只撤回这个账号的指定授权。
        $message = "用户 $uid 已删除权限 $name ！";
    }
}
$grants = Privilege::holders(); // 列表反映本次增删完成后的授权。
$page = (int)($_GET['page'] ?? 1);
$offset = 检测页面(count($grants), $page); // 分页对象是授权记录，一个用户可能占多行。
?>
<div class='row-fluid'>
<form method="post"><?=formTokenField()?>
<b>为用户添加权限</b>
用户编号：<input type="number" name="uid" value="<?=$uid?>" />
<input type="hidden" name="way" value="ins" />
用户权限：<select name="pri">
<?php foreach (Privilege::NAMES as $name => $id) { ?>
<option value="<?=$id?>" <?=$number === $id ? 'selected' : ''?>><?=h($name)?></option>
<?php } ?>
</select>
<button type="submit" class="btn btn-primary">添加权限</button>
</form>
<?php if ($message !== '') { ?><div class="alert alert-success"><?=h($message)?></div><?php } ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>用户</th>
    <th>权限</th>
    <th>可用</th>
    <th>操作</th>
  </tr>
<?php foreach (array_slice($grants, $offset, (int)$SET['style_pagesize']) as $grant) { ?>
  <tr>
    <td><a href='../user/detail.php?uid=<?=$grant['uid']?>' target='_blank'><?=gravatar::showImage($grant['email']);?><?=h($grant['nickname'])?></a></td>
    <td><?php echo h(array_search($grant['pri'], Privilege::NAMES)) ?></td>
    <td><?php if ($grant['def']) echo "是"; else echo "否"; ?></td>
    <td>
    <a href='privilege.php?way=edit&uid=<?=$grant['uid']?>'>编辑</a>
    <a href='privilege.php?way=del&uid=<?=$grant['uid']?>&pri=<?=$grant['pri']?>'>删除</a>
    </td>
<?php
	}
?>
</table>
<?php
分页(count($grants), $page);
?>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
