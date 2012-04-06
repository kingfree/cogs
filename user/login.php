<?php
require_once("../include/stdhead.php");
if ($_SESSION['ID'])header("location: ../");
if (isset($_COOKIE['cojs_login']) && isset($_COOKIE['User'])) header("location: dologin.php?from={$_GET['from']}");
gethead(1,"","用户登录");
?>

<p><a href="../">返回首页</a></p>
<center>
<form id="Login" name="Login" method="post" action="dologin.php">
  <table id=login>
    <tr>
      <th scope="row">用户名</th>
      <td align=left><input name="username" type="text" id="username" /></td>
    </tr>
    <tr>
      <th scope="row">密　码</th>
      <td align=left><input name="password" type="password" id="password" /></td>
    </tr>
    <!--<tr>
      <th scope="row">验证码</th>
      <td align=left valign=center><input name="VerifyCode" type="text" class="InputBox" id="VerifyCode" size="8" maxlength="4" />
      <img src="../include/verifycode.php" /></td>
    </tr>-->
    <tr><td></td>
<td><input type="submit" name="Login" value=" 登录 " style="font-size:25px;" />
<input name="savepwd" type="checkbox" id="savepwd" value="1" /><label for="savepwd">自动登录<label></td>
    </tr>
    <tr>
<input name="from" type="hidden" id="from" value="<?=$_GET['from'] ?>" />
 <td></td>
<td><a href="lost.php">忘记密码</a></td>
   </tr>
    <tr>
 <td></td>
<td><a href="register.php">立即注册</a></td>
   </tr>
  </table>
</form>
</center>

<?php
	include_once("../include/stdtail.php");
?>
