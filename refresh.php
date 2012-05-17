<?php
require_once("./include/stdhead.php");
gethead(1,"","提示");
?>
  <table width="100%" border="1">
    <tr>
      <th bgcolor="#FFFFFF"><div align="center">
        <?php echo $STR[info][done]; ?>
      </div></th>
    </tr>
    <tr>
      <td bgcolor="#FFFF99"><div align="center">
<?php 
	switch($_GET[id])
	{
	case 1:
		echo $STR[info][editenv]; 
		$urlgo="./admin";
		break;
	case 2:
		echo $STR[info][loginsucc]; 
		$urlgo=base64_decode($_GET['go']);
		break;
	case 3:
		echo $STR[info][logout]; 
		$_SESSION=array();
		session_destroy();
		$urlgo="./";
		break;
	case 4:
		echo $STR[info][regsucc]; 
		$urlgo="./";
		break;
	case 5:
		echo $STR[info][editusersucc]; 
		$urlgo="./user/index.php";
		break;
	case 6:
		echo $STR[info][resetdatasucc]; 
		$urlgo="./admin/settings.php?settings=dbctrl";
		break;
	case 7:
		echo $STR[info][editprobsucc]; 
		if ((int)$_GET['pid'])
			$urlgo="./problem/problem.php?pid={$_GET['pid']}";
		else
			$urlgo="./problem/index.php";
		break;
	case 8:
		echo $STR[info][editusersucc]; 
		$urlgo="./user/panel.php";
		break;
	case 9:
		echo $STR[info][comments]; 
		$urlgo="./problem/comments.php?pid={$_GET['pid']}";
		break;
	case 10:
		echo $STR[info][editcommentssucc]; 
		$urlgo="./admin/settings.php?settings=comments";
		break;
	case 11:
		echo $STR[info][editgroupsucc]; 
		$urlgo="./information/grouplist.php";
		break;
	case 12:
		echo $STR[info][editcatesucc]; 
		$urlgo="./information/catelist.php";
		break;
	case 13:
		echo $STR[info][editfilesucc]; 
		$urlgo="./admin/";
		break;
	case 14:
		echo $STR[info][editcompbasesucc]; 
		$urlgo="./admin/settings.php?settings=comp";
		break;
	case 15:
		echo $STR[info][editcomptimesucc]; 
		$urlgo="./admin/settings.php?settings=comp";
		break;
	case 16:
		echo "比赛题目提交成功"; 
		$urlgo="./competition";
		break;
	case 17:
		echo "评测机修改成功"; 
		$urlgo="./information/grader.php";
		break;
	case 18:
		echo "参数修改成功"; 
		$urlgo="./admin/settings.php?settings=settings";
		break;
	case 19:
		echo "排名生成成功"; 
		$urlgo="./admin/settings.php?settings=rank";
		break;
	case 20:
		echo "申请成功，请等待验证"; 
		$urlgo="./information/grouplist.php";
		break;
	case 21:
		echo "处理申请成功"; 
		$urlgo="./admin/group/apply.php";
		break;
	case 22:
		echo "邮件发送成功"; 
		$urlgo="./mail/index.php";
		break;
	case 23:
		echo "页面修改成功！"; 
		if ((int)$_GET['aid'])
			$urlgo="./page/page.php?aid={$_GET['aid']}";
		else
			$urlgo="./page/index.php";
		break;
	}
?>
</div></td>
    </tr>
    <tr>
      <td bgcolor="#66FF99"><div align="center"><a href=<?php echo $urlgo ?>><?php echo $SET['style_jumptime'].$STR[info][jump]; ?></a></div></td>
    </tr>
  </table>

<meta http-equiv=refresh content=<?php echo $SET['style_jumptime'] ?>;URL=<?php echo $urlgo ?>>

<?php
	include_once("./include/stdtail.php");
?>
