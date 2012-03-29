<?php
require_once("../include/stdhead.php");
gethead(1,"sess","修改密码");
?>

<a href="panel.php">用户控制面板</a>
<form id="form1" name="form1" method="post" action="doedit.php?uid=<?php echo $_SESSION[ID] ?>">
  <table width="100%" border="0">
    <tr>
      <td>原密码</td>
      <td><input name="opwd" type="password" id="opwd" class="InputBox" /></td>
    </tr>
    <tr>
      <td>新密码</td>
      <td><input name="npwd1" type="password" id="npwd1" class="InputBox" /></td>
    </tr>
    <tr>
      <td>重复输入新密码</td>
      <td><input name="npwd2" type="password" id="npwd2" class="InputBox" /></td>
    </tr>
  </table>
  <p>
    <input type="submit" name="Submit" value="修改" class="Button" />
    <input name="action" type="hidden" id="action" value="editpwd" />
  </p>
</form>

<?php
	include_once("../include/stdtail.php");
?>