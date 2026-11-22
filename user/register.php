<?php
require_once("../include/header.php");
gethead(1,"","用户注册");
if(!$SET['limit_regallow']) 异常("暂时不允许注册！");
if($_SESSION['ID']) 异常("已经登录！");
?>
<div class='container'>
<?php
if (($_GET['accept'] ?? '') == 1) {
?>
<form id='zhuce' method="post" action="doreg.php" class='form-inline'><?=formTokenField()?>
<table class='table-form'>
<tr>
<th width='100px'>用户名称</th>
<td><input name="usr" type="text" required pattern="[A-Za-z0-9_]{1,24}" maxlength="24" id="usr" /> 1～24位，遵循变量名命名规范</td>
</tr>
<tr>
<th>密码</th>
<td><input name="pwd" type="password" required autocomplete="new-password" id="pwd"> 1 至 72 字节</td>
</tr>
<tr>
<th>重复密码</th>
<td><input name="repwd" type="password" required autocomplete="new-password" id="repwd" /> 验证你没有输错</td>
</tr>
<tr>
<th>用户昵称</th>
<td><input name="nickname" type="text" id="nickname" required maxlength="16" /> 1～16位，汉字、字母、假名、谚文等可显示字符</td>
</tr>
<tr>
<th>电子邮件</th>
<td><input name="email" type="email" required id="email" /> 用于显示 Gravatar 头像</td>
</tr>
<tr>
<th>提示问题</th>
<td><input name="passwordtip" type="text" id="passwordtip" maxlength="64" /> 0～64位可显示字符，用于忘记密码时找回密码</td>
</tr>
<tr>
<th>问题答案</th>
<td><input name="passwordtipans" type="text" id="passwordtipans" /> 最多 72 字节，上述问题的答案，保存为密码哈希</td>
</tr>
<tr>
<th>真实姓名</th>
<td><input name="realname" type="text" id="realname" maxlength="16" /> 0～16位汉字字符，不会公开，<b>如果你实在不想写的话写可以代表你的昵称也可以</b></td>
</tr>
<tr>
<th>个人介绍</th>
<td><textarea name="memo" class='textarea' id="memo">写写你的座右铭、个人宣言、联系方式什么的都可以</textarea></td>
</tr>
<tr>
<th>验证码</th>
<td>
<input name="VerifyCode" type="text" id="VerifyCode" size="8" maxlength="4" />
<img src="../include/verifycode.php" />
请输入这个只含有数字和字母的4位验证码，以确认你是人
</td>
</tr>
<tr><td></td><td>
<input type='submit' class="btn btn-primary" value="注册" />
</td></tr>
</table>
</form>

<?php
} else {
?>
<div align="center">
<div class='page'>
<?php echo $SET['reg_eula']; ?>
<a href="register.php?accept=1" class='btn btn-primary'>同意</a>
<a href="../" class='btn'>拒绝</a>
</div>
</div>
<?php
}
?>
</div>

<?php
include_once("../include/footer.php");
?>
