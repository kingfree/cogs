<?php
require_once("../include/header.php");
gethead(1,"","用户注册");
?>

<script language="javascript">
function IsDigit(cCheck) {
    return (('0'<=cCheck) && (cCheck<='9')); 
}

function IsAlpha(cCheck) {
    return ((('a'<=cCheck) && (cCheck<='z')) || (('A'<=cCheck) && (cCheck<='Z')));
} 

function Verify(p) {
    strUserID = p.value;
    if (strUserID == "") {
        p.focus();
        return false;
    }
    for (nIndex=0; nIndex<strUserID.length; nIndex++) {
        cCheck = strUserID.charAt(nIndex);
        if ( nIndex==0 && ( cCheck =='-' || cCheck =='_') ) {
            p.focus();
            return false;
        }
		if (!(IsDigit(cCheck) || IsAlpha(cCheck) || cCheck=='-' || cCheck=='_' )) {
			p.focus();
			return false;
		}
	}
	return true;
} 

function formsubmit() {
	//检查是否check通过
	len=document.form.usr.value.length;
	if (len<4 || len>24 || !Verify(document.form.usr)) {
		alert("用户名长度必须在[4,24]中且只能使用英文字母、数字以及-和_，并且首字符必须为字母或数字。");
		return;
	}
	len=document.form.nickname.value.length;
	if (len<2 || len>20 ) {
		alert("昵称长度必须在[2,20]中。");
		return;
	}
	len=document.form.pwd.value.length;
	if (len<4 || len>24 || !Verify(document.form.pwd)) {
		alert("密码长度必须在[4,24]中。且只能使用英文字母、数字以及-和_，并且首字符必须为字母或数字。");
		return;
	}
	if (document.form.pwd.value!=document.form.repwd.value)	{
		alert("重复输入密码必须和密码相同。");
		return;
	}
	len=document.form.passwordtip.value.length;
	if (len<4 || len>64 ) {
		alert("密码提示问题长度必须在[4,64]中。");
		return;
	}
	len=document.form.passwordtipans.value.length;
	if (len<4 || len>64 ) {
		alert("密码提示问题答案长度必须在[4,64]中。");
		return;
	}
	len=document.form.memo.value.length;
	if (len<4 || len>200 ) {
		alert("个人介绍答案长度必须在[4,200]中。");
		return;
	}
	document.form.submit();
	return ;
}
</script>
<div class='container'>
<?php
if ($_GET[accept]==1) {
?>
<form method="post" action="doreg.php" class='form-inline'>
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
<td><input name="repwd" type="password" id="password" /> 验证你没有输错</td>
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
<td><input name="passwordtip" type="text" id="passwordtip" /> 用于忘记密码时找回密码（4～64位字符）</td>
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
<td><textarea name="memo" class='textarea' id="memo">这家伙很懒，什么都没写</textarea> 4～200位可显示字符</td>
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
<input type="button" onclick="formsubmit()" class="btn btn-primary" value="注册" />
</td></tr>
</table>
</form>
<?php
} else {
?>
<div align="center">
<div class='page'>
<?php echo $STR[reg][reginfo]; ?>
</div>
<a href="register.php?accept=1" class='btn btn-primary'>同意</a>
<a href="../" class='btn'>拒绝</a>
</div>
<?php
}
?>
</div>
<?php
include_once("../include/footer.php");
?>
