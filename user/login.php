<?php
require_once("../include/stdhead.php");
if ($_SESSION['ID'])header("location: ../");
if (isset($_COOKIE['cojs_login']) && isset($_COOKIE['User'])) header("location: dologin.php?from={$_GET['from']}");
gethead(1,"","用户登录");
?>

<p><a href="../">返回首页</a></p>
<center>
<form id="Login" name="Login" method="post" action="dologin.php">
  <table border="0">
    <tr>
      <th scope="row">用户名</th>
      <td><input name="username" type="text" id="username" class="InputBox" /></td>
    </tr>
    <tr>
      <th scope="row">密　码</th>
      <td><input name="password" type="password" id="password" class="InputBox" /></td>
    </tr>
    <tr>
      <th scope="row">验证码</th>
      <td><input name="VerifyCode" type="text" class="InputBox" id="VerifyCode" size="8" maxlength="4" />
      <img src="../include/verifycode.php" /></td>
    </tr>
  </table>
  <p><input type="submit" name="Login" value=" 登录 " class="Button" />
<input name="savepwd" type="checkbox" id="savepwd" value="1" /><label for="savepwd">自动登录<label>
<input name="from" type="hidden" id="from" value="<?=$_GET['from'] ?>" />
</p>
  <p><a href="lost.php">忘记密码</a>    <a href="register.php">立即注册</a></p>
</form>
</center>

<?php
	include_once("../include/stdtail.php");
?>
