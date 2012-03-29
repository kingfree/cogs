<?php
require_once("./include/stdhead.php");
gethead(1,"","错误");
?>

  <table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
    <tr>
      <th bgcolor="#FFFFFF"><div align="center">
        <?php echo $STR[err][done]; ?>
      </div></th>
    </tr>
    <tr>
      <td bgcolor="#FFFF99"><div align="center">
<?php 
	switch($_GET[id])
	{
	case 1:
		echo $STR[err][sess]; 
		$urlgo="./user/login.php?from={$_GET['from']}";
		break;
	case 2:
		echo $STR[err][usr]; 
		$urlgo="./";
		break;
	case 3:
		echo "登录失败(密码/验证码错误)"; 
		$urlgo="./user/login.php";
		break;
	case 4:
		echo $STR[err][reg]; 
		$urlgo="./user/register.php";
		break;
	case 5:
		echo $STR[err][regusr]; 
		$urlgo="./user/register.php?accept=1";
		break;
	case 6:
		echo $STR[err][regin]; 
		$urlgo="./user/register.php";
		break;
	case 7:
		echo $STR[err][force]; 
		$urlgo="./";
		break;
	case 8:
		echo $STR[err][from]; 
		$urlgo="./";
		break;	
	case 9:
		echo $STR[err][usr]; 
		$urlgo="./admin/settings.php?settings=userlist";
		break;	
	case 10:
		echo $STR[err][force]; 
		$urlgo="./admin/settings.php?settings=grouplist";
		break;	
	case 11:
		echo $STR[err][usr]; 
		$urlgo="./";
		break;	
	case 12:
		echo $STR[err][prob]; 
		$urlgo="./admin/settings.php?settings=problist";
		break;	
	case 13:
		echo $STR[err][pmis]; 
		$urlgo="./user/editpwd.php";
		break;	
	case 14:
		echo $STR[err][pwd]; 
		$urlgo="./user/editpwd.php";
		break;	
	case 15:
		echo $STR[err][pwdans]; 
		$urlgo="./user/lost.php";
		break;	
	case 16:
		echo $STR[err][nosub]; 
		$urlgo="./admin/settings.php?settings=submit";
		break;	
	case 17:
		echo $STR[err][force]; 
		$urlgo="./problem/problist.php";
		break;	
	case 18:
		echo $STR[err][unsubmitable]; 
		$urlgo="./problem/problist.php";
		break;	
	case 19:
		echo $STR[err][comptime]; 
		$urlgo="./admin/settings.php?settings=comp";
		break;
	case 20:
		echo "比赛已结束"; 
		$urlgo="./competition";
		break;
	case 21:
		echo "比赛还未开始"; 
		$urlgo="./competition";
		break;
	case 22:
		echo "邮件发送失败！"; 
		$urlgo="./mail";
		break;
	}
?>
</div></td>
    </tr>
    <tr>
      <td bgcolor="#66FF99"><div align="center"><a href=<?php echo $urlgo ?>><?php echo $SETTINGS['style_jumptime'].$STR[info][jump]; ?></a></div></td>
    </tr>
  </table>

<meta http-equiv=refresh content=<?php echo $SETTINGS['style_jumptime'] ?>;URL=<?php echo $urlgo ?>>

<?php
	include_once("./include/stdtail.php");
?>
