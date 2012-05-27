<?php
require_once("../include/stdhead.php");
gethead(1,"sess","控制面板");

include_once("editpwd.php");

?>

<?php
$p=new DataAccess();
$sql="select * from userinfo where uid={$_SESSION[ID]}";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);
?>
<form method="post" action="doedit.php?uid=<?=$_SESSION[ID] ?>" enctype="multipart/form-data" class='form-inline' >
<input name="action" type="hidden" value="edit" />
<input name="uid" type="hidden" value="<?=$d['uid']?>" />
<table id="userpanel" class='table-form'>
  <tr>
    <th width='80px'>用户编号</th>
    <td><?=$d['uid'] ?></td>
    <th width='300px'>用户头像</th>
  </tr>
  <tr>
    <th>用户名称</th>
    <td><?=gravatar::showImage($d['email']);?><?=$d['usr'] ?></td>
    <td rowspan='8' class='center'>
    <?=gravatar::showImage($d['email'], 200);?><br />
    <div>
    </div>
    </td>
  </tr>
  <tr>
    <th>用户昵称</th>
    <td><input name="nick" value="<?=$d['nickname']?>" /> 不要太长，否则会导致在首页上显示得很难看</td>
  </tr>
  <tr>
    <th>真实姓名</th>
    <td><input name="realname" value="<?=$d['realname']?>" /> 如果以前没有填过或者填的不是的话请注意修改</td>
  </tr>
  <tr>
    <th>E-mail</th>
    <td><input name="email" type="email" value="<?=$d['email']?>" /> 这个电子邮箱现在用于显示用户头像，请一律小写</td>
  </tr>
  <tr>
    <th>等级</th>
    <td><b><?=$d[grade] ?></b>
 = 求和(题目的最高得分 * 题目的难度 / 30)
    </td>
  </tr>
  <tr>
    <th>背景图片</th>
    <td><input type="file" name="file" /> 在此选择系统背景图片，请手动清除浏览器缓存</td>
  </tr>
  <tr>
    <th>个人介绍</th>
    <td><textarea name="memo" class='span8' ><?=$d['memo'] ?> </textarea></td>
  </tr>
  <tr>
    <th></th>
    <td>
    <a class='btn' href="#editpwd" data-toggle='modal'>修改密码</a>
    <button type="submit" class='btn btn-primary'>确认以上修改</button>
    <a href="dodelimg.php?email=<?=md5($d['email'])?>" class='btn pull-right'>重建头像缓存</a>
    <a href="http://cn.gravatar.com" target='_blank' class='btn pull-right' title='Gravatar - 全球公认的头像' rel='popover' data-content='<p class="alert"><span class="label label-warning">注意</span>此网站在中国大陆访问可能会出现问题，请自行想办法解决。</p><p>首先点此按钮进入 Gravatar 网站，点击上方下滑菜单中的注册，输入你的电子邮件地址（对，就是你在 COGS 使用的邮箱），点击注册后会想你邮箱中发送一封验证邮件，以此连接验证身份后可在网站注册用户。</p><p class="alert alert-info">上传头像并裁剪后会有评级系统，你只需要选择<span class="badge badge-info">G(普通级)</span>即可。</p>'>上传头像</a>
    </td>
  </tr>
</table>
</form>

<?php
	include_once("../include/stdtail.php");
?>
