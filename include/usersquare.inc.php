<?php

function showuser($uid,$user_access)
{
	global $SETTINGS,$STR;
	$portrait=$SETTINGS['base']."images/portrait";
	$index=$SETTINGS['base']."index.php";
	$udetail=$SETTINGS['base']."user/detail.php?uid={$uid}";
	$userlist=$SETTINGS['base']."information/userlist.php";
	$login=$SETTINGS['base']."user/login.php";
	
	if ((int)$uid)
	{
		$sql="select * from userinfo,groups where uid='{$uid}' and groups.gid=userinfo.gbelong";
		$user_access->dosql($sql);
		$d=$user_access->rtnrlt(0);
	
	?>
	<table border="0">
	  <tr>
		<td><table border="0">
		  <tr>
			<td align="center" valign="top"><a href="<?php echo pathconvert($SETTINGS['cur'],$index);?>"><?=gravatar::showImage($d['email'], 64);?>
<!--<img src="<?php echo pathconvert($SETTINGS['cur'],$portrait).'/'.$d['portrait']?>.jpg" border="0" width="64" height="64" />--></a> </td>
		  </tr>
		  <tr>
			<th><a href="<?php echo pathconvert($SETTINGS['cur'],$udetail);?>"><?php echo $d['nickname'];?></a></th>
		  </tr>
		</table></td>
		<td><table border="0">
		  <tr>
			<td>通过</td>
			<td><?php echo $d['accepted'] ?>/<?php echo $d['submited'] ?> (<?php @printf("%.2f",$d['accepted']/$d['submited']*100) ?>%)</td>
		  </tr>
		  <tr>
			<td>等级</td>
			<td><?php echo $d['grade'] ?></td>
		  </tr>
		  <tr>
			<td>分组</td>
			<td><a href="<?php echo pathconvert($SETTINGS['cur'],$userlist);?>?gid=<?php echo $d['gid'] ?>"><?php echo $d['gname'] ?></a></td>
		  </tr>
		  <tr>
			<td>权限</td>
			<td><?php echo $STR[adminn][$d['admin']]; ?></td>
		  </tr>
		</table>
		</td>
	  </tr>
	</table>
	
	<? } else { ?>
	<table border="0">
	  <tr>
		<td><table border="0">
		  <tr>
			<td align="center" valign="top"><a href="<?php echo pathconvert($SETTINGS['cur'],$index);?>"><img src="<?php echo pathconvert($SETTINGS['cur'],$portrait).'/0'?>.jpg" border="0" width="64" height="64" /></a> </td>
		  </tr>
		</table></td>
		<td><table border="0">
		  <tr>
			<td><a href="<?php echo pathconvert($SETTINGS['cur'],$login)."?from=".$SETTINGS['URI'] ?>">请点此登录</a></td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		</table></td>
	  </tr>
	</table>
<?php
	}
}

?>
