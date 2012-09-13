<?php
require_once("../include/header.php");
gethead(1,"","用户注册");
if(!$SET['limit_regallow']) 异常("暂时不允许注册！");
if($_SESSION['ID']) 异常("已经登录！");
?>
<div class='container'>
<?php
if ($_GET[accept]==1) {
?>
<form id='zhuce' method="post" action="doreg.php" class='form-inline'>
<table class='table-form'>
<tr>
<th width='100px'>用户名称</th>
<td><input name="usr" type="text" id="usr" /> 4～24位数字或字母</td>
</tr>
<tr>
<th>密码</th>
<td><input name="pwd" type="password" id="pwd"></td>
</tr>
<tr>
<th>重复密码</th>
<td><input name="repwd" type="password" id="repwd" /> 验证你没有输错</td>
</tr>
<tr>
<th>用户昵称</th>
<td><input name="nickname" type="text" id="nickname" /> 2～10位可显示字符</td>
</tr>
<tr>
<th>电子邮件</th>
<td><input name="email" type="text" id="email" /> 用于显示 Gravatar 头像</td>
</tr>
<tr>
<th>提示问题</th>
<td><input name="passwordtip" type="text" id="passwordtip" /> <b>不能更改！</b> 用于忘记密码时找回密码（4～64位字符）</td>
</tr>
<tr>
<th>问题答案</th>
<td><input name="passwordtipans" type="text" id="passwordtipans" /> 上述问题的答案（4～64位字符）</td>
</tr>
<tr>
<th>真实姓名</th>
<td><input name="realname" type="text" id="realname"/> 4～64位字符</td>
</tr>
<tr>
<th>个人介绍</th>
<td><textarea name="memo" class='textarea' id="memo">这家伙很懒，什么都没写</textarea></td>
</tr>
<tr>
<th>验证码</th>
<td>
<input name="VerifyCode" type="text" id="VerifyCode" size="8" maxlength="4" />
<img src="../include/verifycode.php" />
请输入这个只含有数字和字母的4位验证码以确认你是人
</td>
</tr>
<tr><td></td><td>
<input type='submit' class="btn btn-primary" value="注册" />
</td></tr>
</table>
</form>
<script language="javascript">
$("#zhuce").submit(function() {
  var t = $("#usr").val();
  var e = /([a-z0-9][_a-z0-9]{3,23})/;
  if(!e.test(t)) {
    alert("用户名长度必须在[4,24]中且只能使用英文字母、数字以及_，并且首字符必须为字母或数字。");
    return false;
  }
  $.get("checkname.php",{name: t},function(txt){
    if(txt) {
      alert("用户名已被注册！");
      return false;
    }
  });
  t = $("#pwd").val();
  e = /(.{4,24})/;
  if(!e.test(t)) {
    alert("密码长度必须在[4,24]中。");
    return false;
  }
  if($("#pwd").val() != $("#repwd").val()) {
    alert("重复输入密码必须和密码相同。");
    return false;
  }
  t = $("#nickname").val();
  e = /(\S{2,20})/;
  if(!e.test(t)) {
    alert("昵称长度必须在[2,20]中。");
    return false;
  }
  t = $("#email").val();
  e = /(\S*@\S*\.\S*)/;
  if(!e.test(t)) {
    alert("电子邮箱格式不正确。");
    return false;
  }
  t = $("#realname").val();
  e = /(\S{0,20})/;
  if(!e.test(t)) {
    alert("真实姓名长度必须在[0,20]中。");
    return false;
  }
  t = $("#passwordtip").val();
  e = /(.{2,64})/;
  if(!e.test(t)) {
    alert("提示问题长度必须在[2,64]中。");
    return false;
  }
  t = $("#passwordtipans").val();
  e = /(.{2,64})/;
  if(!e.test(t)) {
    alert("提示问题答案长度必须在[2,64]中。");
    return false;
  }
  $.get("checkname.php",{code: $("#VerifyCode").val()},function(txt){
    if(txt == 0) {
      alert("验证码错误，请一律小写。");
      return false;
    }
  });
  return true;
});
</script>
<?php
} else {
?>
<div align="center">
<div class='page'>
<?php echo $SET[reg_eula]; ?>
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
