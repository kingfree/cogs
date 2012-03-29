<?php
require_once("../include/stdhead.php");

switch ($_REQUEST['settings'])
{
	case "phpinfo":
		gethead(0,"advadmin","phpinfo");
		break;
	case "terminal":
		gethead(1,"advadmin","PHP终端访问");
		break;
	case "grader":
		gethead(1,"advadmin","评测机管理");
		break;
	case "dbctrl":
		gethead(1,"advadmin","数据库管理");
		break;
	case "serverinfo":
		gethead(1,"advadmin","服务器信息");
		break;
	case "settings":
		gethead(1,"advadmin","参数设置");
		break;
	case "problist":
		gethead(1,"admin","题目管理");
		break;
	case "submit":
		gethead(1,"admin","提交管理");
		break;
	case "comments":
		gethead(1,"admin","评论管理");
		break;
	case "category":
		gethead(1,"admin","分类管理");
		break;
	case "userlist":
		gethead(1,"admin","用户管理");
		break;
	case "grouplist":
		gethead(1,"admin","用户组管理");
		break;
	case "comp":
		gethead(1,"admin","比赛管理");
		break;
	case "rank":
		gethead(1,"admin","排名管理");
		break;
}
?>

<p><a href="./"><?php echo $STR[admin][backindex]; ?></a></p><?php
switch ($_REQUEST['settings'])
{
	case "phpinfo":
		phpinfo();
		break;
	case "terminal":
		require("terminal/terminal.php");
		break;
	case "grader":
		require("grader/grader.php");
		break;
	case "dbctrl":
		require("dbctrl/setdata.php");
		break;
	case "serverinfo":
		require("serverinfo/serverinfo.php");
		break;
	case "settings":
		require("settings/settings.php");
		break;
	case "problist":
		require("problem/problist.php");
		break;
	case "submit":
		require("record/submitlist.php");
		break;
	case "comments":
		require("record/commentslist.php");
		break;
	case "category":
		require("category/catelist.php");
		break;
	case "userlist":
		require("user/userlist.php");
		break;
	case "grouplist":
		require("group/grouplist.php");
		break;
	case "comp":
		require("comp/compbase.php");
		break;
	case "rank":
		require("rank/rank.php");
		break;
}
?>

<?php
	include_once("../include/stdtail.php");
?>