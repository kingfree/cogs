<?php
require_once("../include/stdhead.php");
gethead(1,"sess","个人中心");
?>

<p><a href="../information/submitlist.php?uid=<?php echo $_SESSION['ID'] ?>">查看我的提交记录</a></p>
<p><a href="editpwd.php" class="LinkButton">修改密码</a></p>

<?php
$p=new DataAccess();
$sql="select * from userinfo where uid={$_SESSION[ID]}";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);

?>
<form id="form" name="form" method="post" action="doedit.php?uid=<?php echo $_SESSION[ID] ?>">
<table width="100%" border="1"   bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td width="15%">用户ID</td>
    <td width="85%"><?php echo $d[uid] ?></td>
  </tr>
  <tr>
    <td>用户名</td>
    <td><?php echo $d[usr] ?></td>
  </tr>
  <tr>
    <td>昵称</td>
    <td><input class="InputBox" name="nick" value=<?=$d[nickname]?> /> 不要太长，否则会导致在首页上显示得很难看</td>
  </tr>
  <tr>
    <td>真实姓名</td>
    <td><input class="InputBox" name="realname" value=<?=$d[realname]?> /> 如果以前没有填过或者填的不是的话请注意修改</td>
  </tr>
  <tr>
    <td>E-mail</td>
    <td><input class="InputBox" name="email" type="email" value=<?=$d[email]?> /> 这个电子邮箱现在用于显示用户头像，请一律小写</td>
  </tr>
  <tr>
    <td>阅读权限</td>
    <td><?php echo $d[readforce] ?></td>
  </tr>
  <tr>
    <td>管理权限</td>
    <td><?php echo $STR[adminn][$d[admin]] ?></td>
  </tr>
  <tr>
    <td>等级</td>
    <td><b><?php echo $d[grade] ?></b> 等级 = (通过题目难度之和 * 2000) / 提交题目数</td>
  </tr>
  <tr>
    <td>个人介绍</td>
    <td><textarea name="memo" cols="80" rows="10" class="TextArea"><?php echo $d[memo] ?> </textarea></td>
  </tr>
  <tr>
    <td>头像</td>
    <td>
<?=gravatar::showImage($d['email'], 64);?>
<a href="dodelimg.php?email=<?=md5($d['email'])?>" >清空头像缓存</a>
</td>
  </tr>
  <tr>
    <td>注册时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d['regtime']) ?></td>
  </tr>
</table>

<p>
  <input type="submit" name="Submit" value="修改"  class="Button"/>
  <input name="action" type="hidden" id="action" value="edit" />
  <input name="uid" type="hidden" id="uid" value="<?=$d['uid']?>" />
</p>
</form>

<?php
	include_once("../include/stdtail.php");
?>
