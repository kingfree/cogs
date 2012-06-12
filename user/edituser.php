<?php
require_once("../include/header.php");
gethead(1,"修改用户","修改用户");
?>

<?php
$p=new DataAccess();
$sql="select * from userinfo where uid={$_GET[uid]}";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$d=$p->rtnrlt(0);
?>
<form id="form1" name="form1" method="post" action="doedituser.php?action=edit&uid=<?php echo $_GET[uid]; ?>">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td scope="col">修改用户</td>
    <td scope="col"><b><?php echo $d[usr] ?></b></td>
  </tr>
  <tr>
    <td>昵称</td>
    <td>
      <input name="nickname" type="text" value="<?php echo $d[nickname] ?>" ></td>
  </tr>
  <tr>
    <td>真实姓名</td>
    <td><input name="realname" type="text" value="<?php echo $d[realname] ?>"></td>
  </tr>
  <tr>
    <td>E-mail</td>
    <td><input class="InputBox" name="email" type="email" value=<?=$d[email]?>></td>
  </tr>
  <tr>
    <td>阅读权限</td>
    <td><input name="readforce" type="text" value="<?php echo $d[readforce] ?>"></td>
  </tr>
  <tr>
    <td>所属分组</td>
    <td><select name="gbelong" id="gbelong">
<?php 
$q=new DataAccess();
$sql2="select * from groups";
$cnt2=$q->dosql($sql2);
for ($i=0;$i<=$cnt2-1;$i++)
{
	$e=$q->rtnrlt($i);
?>
      <option value="<?php echo $e[gid]; ?>" <?php if ($d[gbelong]==$e[gid]) echo 'selected="selected"'; ?>><?php echo $e[gname]; ?></option>
<?php 
}
?>
    </select></td>
  </tr>
  <tr>
    <td>积分</td>
    <td><input name="grade" type="text" value="<?php echo $d[grade] ?>"></td>
  </tr>
  <tr>
    <td>个人介绍</td>
    <td><textarea name="memo" class="textarea"><?php echo $d[memo] ?></textarea></td>
  </tr>
  <tr>
    <td>重置密码(空)</td>
    <td>
<input name="reset" type="checkbox" id="reset" value="reset" />    </td>
  </tr>
</table>
<p>
  <input type="submit" name="Submit" value="提交修改"  class="Button" />
  <input name="comefrom" type="hidden" id="comefrom" value="edituser" />
</form>
<?php
}
else
{
    异常("无此用户！",取路径("user/index.php"));
}
?>

<?php
	include_once("../include/footer.php");
?>
