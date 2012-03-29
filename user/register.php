<?php
require_once("../include/stdhead.php");
gethead(1,"","用户注册");
?>

<p><a href="../">返回首页</a></p>
<script language="javascript">

function IsDigit(cCheck) 
{ 
	return (('0'<=cCheck) && (cCheck<='9')); 
}

function IsAlpha(cCheck) 
{
	return ((('a'<=cCheck) && (cCheck<='z')) || (('A'<=cCheck) && (cCheck<='Z')));
} 

function Verify(p)
{
	strUserID = p.value;
	if (strUserID == "")
	{
		p.focus();
		return false;
	}
	for (nIndex=0; nIndex<strUserID.length; nIndex++)
	{
		cCheck = strUserID.charAt(nIndex);
		if ( nIndex==0 && ( cCheck =='-' || cCheck =='_') )
		{
			p.focus();
			return false;
		}
		
		if (!(IsDigit(cCheck) || IsAlpha(cCheck) || cCheck=='-' || cCheck=='_' ))
		{
			p.focus();
			return false;
		}
	}
	return true;
} 


function formsubmit()
{
	//检查是否check通过
	len=document.form.usr.value.length;
	if (len<4 || len>24 || !Verify(document.form.usr))
	{
		alert("用户名长度必须在[4,24]中且只能使用英文字母、数字以及-和_，并且首字符必须为字母或数字。");
		return;
	}
	
	len=document.form.nickname.value.length;
	if (len<2 || len>20 )
	{
		alert("昵称长度必须在[2,20]中。");
		return;
	}
	
	len=document.form.pwd.value.length;
	if (len<4 || len>24 || !Verify(document.form.pwd))
	{
		alert("密码长度必须在[4,24]中。且只能使用英文字母、数字以及-和_，并且首字符必须为字母或数字。");
		return;
	}
	
	if (document.form.pwd.value!=document.form.repwd.value)
	{
		alert("重复输入密码必须和密码相同。");
		return;
	}
	
	len=document.form.passwordtip.value.length;
	if (len<4 || len>64 )
	{
		alert("密码提示问题长度必须在[4,64]中。");
		return;
	}
	
	len=document.form.passwordtipans.value.length;
	if (len<4 || len>64 )
	{
		alert("密码提示问题答案长度必须在[4,64]中。");
		return;
	}
	
	len=document.form.memo.value.length;
	if (len<4 || len>200 )
	{
		alert("个人介绍答案长度必须在[4,200]中。");
		return;
	}
	
	document.form.submit();
	return ;
}
</script>

<div align="center" class="Title">用户注册</div>
<?php
if ($_GET[accept]==1)
{
?>
<form id="form" name="form" method="post" action="doreg.php">
  <table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
    <tr>
      <td><?php echo $STR[reg][user]; ?></td>
      <td><input name="usr" type="text" id="usr" class="InputBox" /></td>
    </tr>
    <tr>
      <td><?php echo $STR[reg][nickname]; ?></td>
      <td><input name="nickname" type="text" id="nickname" class="InputBox" /></td>
    </tr>
    <tr>
      <td><?=$STR[reg][email]?></td>
      <td><input name="email" type="text" id="email" class="InputBox" /></td>
    </tr>
    <tr>
      <td><?php echo $STR[reg][password]; ?></td>
      <td><input name="pwd" type="password" id="pwd" class="InputBox" /></td>
    </tr>
    <tr>
      <td><?php echo $STR[reg][repassword]; ?></td>
      <td><input name="repwd" type="password" id="password" class="InputBox" /></td>
    </tr>
    <tr>
      <td><?php echo $STR[reg][passwordtip]; ?></td>
      <td><input name="passwordtip" type="text" id="passwordtip"  class="InputBox"/></td>
    </tr>
    <tr>
      <td><?php echo $STR[reg][passwordtipans]; ?></td>
      <td><input name="passwordtipans" type="text" id="passwordtipans" class="InputBox" /></td>
    </tr>
    <tr>
      <td><?php echo $STR[reg][realname]; ?></td>
      <td><input name="realname" type="text" id="realname"  class="InputBox"/></td>
    </tr>
    <tr>
      <td><?php echo $STR[reg][memo]; ?></td>
      <td><textarea name="memo" cols="60" rows="4" id="memo" class="TextArea">这家伙很懒，什么都没写</textarea></td>
    </tr>
    <tr>
      <td>验证码</td>
      <td><input name="VerifyCode" type="text" class="InputBox" id="VerifyCode" size="8" maxlength="4" />
      <img src="../include/verifycode.php" /></td>
    </tr>
  </table>
  <p>
    <input type="button" name="Submit" value="注册" onclick="formsubmit()" class="Button"/>
</form>
  <?php
}
else
{
?>
<div align="center">
<textarea name="textarea" cols="120" rows="18" readonly="readonly" class="TextArea"><?php echo $STR[reg][reginfo]; ?></textarea>
</div>
<p align="center"><a href="register.php?accept=1">同意</a> <a href="../">拒绝</a>
<?php
}
?>

<?php
	include_once("../include/stdtail.php");
?>
