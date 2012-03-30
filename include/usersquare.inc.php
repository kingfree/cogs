<?php

function showuser($uid,$user_access)
{
	global $SETTINGS,$STR;
	$portrait=$SETTINGS['base']."images/portrait";
	$index=$SETTINGS['base']."index.php";
	$udetail=$SETTINGS['base']."user/detail.php?uid={$uid}";
	$userlist=$SETTINGS['base']."information/userlist.php";
	$login=$SETTINGS['base']."user/login.php";
	$lost=$SETTINGS['base']."user/lost.php";
	$register=$SETTINGS['base']."user/register.php";
	
	if ((int)$uid)
	{
		$sql="select * from userinfo,groups where uid='{$uid}' and groups.gid=userinfo.gbelong";
		$user_access->dosql($sql);
		$d=$user_access->rtnrlt(0);
	
	?>
<table border="0" style="font-size:12px;">
  <tr>
    <td rowspan=3 align="center"><a href="<?php echo pathconvert($SETTINGS['cur'],$udetail);?>"><?=gravatar::showImage($d['email'], 64);?></a></td>
    <td align=center style="font-size:18px;"><b><?php echo $d['nickname'];?></b></td>
  </tr>
    <td>通过: <?php echo $d['accepted'] ?>/<?php echo $d['submited'] ?> (<?php @printf("%.2f",$d['accepted']/$d['submited']*100) ?>%)</td>
  </tr>
  <tr>
    <td>等级: <?php echo $d['grade'] ?></td>
  </tr>
</table>
	<? } else { ?>
<table border="0">
  <tr>
    <td rowspan=3 align="center"><a href="<?=pathconvert($SETTINGS['cur'],$login)."?from=".$SETTINGS['URI'];?>"><?=gravatar::showImage("cmykrgb123@gmail.com", 64);?></a></td>
<th><a class=LinkButton style="font-size: 18px;" href="<?=pathconvert($SETTINGS['cur'],$login)."?from=".$SETTINGS['URI'];?>">登录</a></th>
  </tr>
  <tr>
<td align="center">
<a href="<?=pathconvert($SETTINGS['cur'],$lost)?>">忘记密码</a></td>
  </tr>
  <tr>
<th><a href="<?=pathconvert($SETTINGS['cur'],$register)."?from=".$SETTINGS['URI'];?>">注册</a></th>
  </tr>
</table>
<?php
	}
}

?>
