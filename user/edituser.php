<?php
// 具有修改用户权限时，通过 user/edituser.php?uid=2 编辑指定账号，勾选重置密码后由 doedituser.php 生成临时密码。
require_once(__DIR__.'/../include/header.php');
gethead(1,"修改用户","修改用户");
?>

<?php
$account = Account::byId((int)($_GET['uid'] ?? 0))[0] ?? null; // 先查原资料再预填表单，以保留这次不改的字段。
if (!$account) 异常('无此用户！', 取路径('user/index.php')); // 不展示可能提交到无效用户的空白编辑表单。
?>
<form id="form1" name="form1" method="post" action="doedituser.php?action=edit&uid=<?=h($_GET['uid'] ?? '')?>"><?=formTokenField()?>
<dl class='dl-horizontal'>
<dt>修改用户</dt>
<dd><b><?=h($account['usr'])?></b></dd>
<dt>昵称</dt>
<dd><input name="nickname" type="text" value="<?=h($account['nickname'])?>" ></dd>
<dt>真实姓名</dt>
<dd><input name="realname" type="text" value="<?=h($account['realname'])?>"></dd>
<dt>E-mail</dt>
<dd><input class="InputBox" name="email" type="email" value="<?=h($account['email'])?>"></dd>
<dt>阅读权限</dt>
<dd><input name="readforce" type="text" value="<?php echo $account['readforce'] ?>"></dd>
<dt>所属分组</dt>
<dd><select name="gbelong" id="gbelong">
<?php
foreach (UserGroup::all() as $group) {
?>
      <option value="<?php echo $group['gid']; ?>" <?php if ($account['gbelong']==$group['gid']) echo 'selected="selected"'; ?>><?=h($group['gname'])?></option>
<?php
}
?>
</select></dd>
<dt>积分</dt>
<dd><input name="grade" type="text" value="<?php echo $account['grade'] ?>"></dd>
<dt>个人介绍</dt>
<dd><textarea name="memo" class="textarea"><?=h($account['memo'])?></textarea></dd>
<dt>重置密码(空)</dt>
<dd>
<input name="reset" type="checkbox" id="reset" value="reset"/>

<input name="comefrom" type="hidden" id="comefrom" value="edituser" />
<input type="submit" name="Submit" value="提交修改" class="btn span2"/>
</dd>
</dl>
</form>
<?php require_once __DIR__.'/../include/footer.php'; ?>
