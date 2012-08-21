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
<dl class='dl-horizontal'>
<dt>修改用户</dt>
<dd><b><?php echo $d[usr] ?></b></dd>
<dt>昵称</dt>
<dd><input name="nickname" type="text" value="<?php echo $d[nickname] ?>" ></dd>
<dt>真实姓名</dt>
<dd><input name="realname" type="text" value="<?php echo $d[realname] ?>"></dd>
<dt>E-mail</dt>
<dd><input class="InputBox" name="email" type="email" value=<?=$d[email]?>></dd>
<dt>阅读权限</dt>
<dd><input name="readforce" type="text" value="<?php echo $d[readforce] ?>"></dd>
<dt>所属分组</dt>
<dd><select name="gbelong" id="gbelong">
<?php 
$q=new DataAccess();
$sql2="select * from groups";
$cnt2=$q->dosql($sql2);
for ($i=0;$i<=$cnt2-1;$i++) {
	$e=$q->rtnrlt($i);
?>
      <option value="<?php echo $e[gid]; ?>" <?php if ($d[gbelong]==$e[gid]) echo 'selected="selected"'; ?>><?php echo $e[gname]; ?></option>
<?php 
}
?>
</select></dd>
<dt>积分</dt>
<dd><input name="grade" type="text" value="<?php echo $d[grade] ?>"></dd>
<dt>个人介绍</dt>
<dd><textarea name="memo" class="textarea"><?php echo $d[memo] ?></textarea></dd>
<dt>重置密码(空)</dt>
<dd>
<input name="reset" type="checkbox" id="reset" value="reset"/>

<input name="comefrom" type="hidden" id="comefrom" value="edituser" />
<input type="submit" name="Submit" value="提交修改" class="btn span2"/>
</dd>
</dl>
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
