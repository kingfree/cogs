<?php
require_once("../include/header.php");
gethead(1,"","用户登录");
if($_SESSION['ID']) 异常("已经登录！");
?>
<form id='denglu' method="post" action="dologin.php" class='form-inline center'>
<input name="username" type="text" class='input' placeholder='用户名' /><br />
<input name="password" type="password" class='input' placeholder='密码' /><br />
<label class="checkbox">
<input name="savepwd" type="checkbox" value="1" />保存 Cookies
</label><br />
<input type='submit' class="btn btn-primary" value="登录" />
<a href="lost.php" class='btn'>忘记密码</a>
<a href="register.php" class='btn btn-danger'>注册</a>
</form>
<?php
include_once("../include/footer.php");
?>
