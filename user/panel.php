<?php
require_once("../include/stdhead.php");
gethead(1,"sess","控制面板");
?>

<?php
$p=new DataAccess();
$sql="select * from userinfo where uid={$_SESSION[ID]}";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);
?>
<form method="post" action="doedit.php?uid=<?=$_SESSION[ID] ?>">
<table id="userpanel">
  <tr>
    <th>用户编号</th>
    <td><?=$d[uid] ?></td>
    <th>用户头像</th>
  </tr>
  <tr>
    <th>用户名称</th>
    <td><?=gravatar::showImage($d['email']);?><?=$d[usr] ?></td>
    <td rowspan=6 align=center>
    <?=gravatar::showImage($d['email'], 200);?><br />
    <a href="dodelimg.php?email=<?=md5($d['email'])?>">清空头像缓存</a><br />
    <div>
      <input type="submit" name="Submit" style="font-size:200%;" value="修改" />
      <input name="action" type="hidden" id="action" value="edit" />
      <input name="uid" type="hidden" id="uid" value="<?=$d['uid']?>" />
    </div>
    <a class=admin href="editpwd.php">修改密码</a>
    </td>
  </tr>
  <tr>
    <th>用户昵称</th>
    <td><input class="InputBox" name="nick" value=<?=$d[nickname]?> /> 不要太长，否则会导致在首页上显示得很难看</td>
  </tr>
  <tr>
    <th>真实姓名</th>
    <td><input class="InputBox" name="realname" value=<?=$d[realname]?> /> 如果以前没有填过或者填的不是的话请注意修改</td>
  </tr>
  <tr>
    <th>E-mail</th>
    <td><input class="InputBox" name="email" type="email" value=<?=$d[email]?> /> 这个电子邮箱现在用于显示用户头像，请一律小写</td>
  </tr>
  <tr>
    <th>等级</th>
    <td><b><?=$d[grade] ?></b> 等级 = (通过题目难度之和 * 2000) / 提交题目数</td>
  </tr>
  <tr>
    <th>个人介绍</th>
    <td><textarea name="memo" cols="60" rows="10" class="TextArea"><?=$d[memo] ?> </textarea></td>
</tr>
  <tr>
  </tr>
</table>
</form>

<?php
	include_once("../include/stdtail.php");
?>
