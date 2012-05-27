<?php
global $STR;

$STR[info][done]="执行成功!";
$STR[info][editenv]="环境变量SET成功";
$STR[info][jump]="秒后将会跳转，如果没有跳转请点击这里";
$STR[info][loginsucc]="登录成功";
$STR[info][logout]="登出成功";
$STR[info][regsucc]="注册成功";
$STR[info][adminindex]="后台管理首页";
$STR[info][login]="用户登录";
$STR[info][userlist]="用户列表";
$STR[info][edituser]="修改用户";
$STR[info][editusersucc]="用户修改成功";
$STR[info][problist]="题目列表";
$STR[info][addprob]="添加题目";
$STR[info][editprob]="修改题目";
$STR[info][editprobsucc]="修改题目成功";
$STR[info][resetdata]="重置数据库将重建所有表并丢失所有数据，而且这个操作可能会持续一段较长的时间，确认重置吗？";
$STR[info][resetdatasucc]="数据库重置成功";
$STR[info][resetingdata]="数据库正在重置";
$STR[info][probdetail]="题目具体内容";
$STR[info][userdetail]="用户详细信息";

$STR[info][probindex]="题库首页";
$STR[info][panel]="控制面板";
$STR[info][comments]="评论发表成功";
$STR[info][commentslist]="评论列表";
$STR[info][editcomments]="修改评论";
$STR[info][editcommentssucc]="评论修改成功";
$STR[info][editgroupsucc]="组修改成功";
$STR[info][editcatesucc]="分类修改成功";
$STR[info][editfilesucc]="文件修改成功";
$STR[info][editcompbasesucc]="比赛基本信息修改成功";
$STR[info][editcomptimesucc]="比赛场次信息修改成功";

$STR[err][nosub]="提交记录不存在";
$STR[err][done]="错误";
$STR[err][sess]="您没有登录";
$STR[err][pwd]="密码错误";
$STR[err][usr]="用户不存在";
$STR[err][reg]="注册信息错误";
$STR[err][regusr]="用户名已经存在";
$STR[err][regin]="注册失败，请与管理员联系";
$STR[err][force]="您没有权限";
$STR[err][from]="您的来源非法";
$STR[err][prob]="题目不存在";
$STR[err][pmis]="两次输入的密码不相同";
$STR[err][pwdans]="密码提示问题答案错误";
$STR[err][unsubmitable]="该题不可提交";
$STR[err][comptime]="场次不存在";

$STR[reg][reginfo]="不得提交有害代码，不得以任何形式对系统进行破坏！";
$STR[reg][user]="用户名(4-24位数字或英文字母)";
$STR[reg][password]="密码(4-24位数字或英文字母)";
$STR[reg][repassword]="重复输入密码";
$STR[reg][passwordtip]="密码提示问题(4-64位字符)";
$STR[reg][passwordtipans]="密码提示问题答案(4-64位字符)";
$STR[reg][nickname]="昵称(2-10位字符)";
$STR[reg][email]="E-mail(用于显示 Gravatar 头像)";
$STR[reg][realname]="真实姓名(2-16位字符)";
$STR[reg][memo]="个人介绍(4-200位字符)";

$STR[lang][0]="Pascal";
$STR[lang][1]="C";
$STR[lang][2]="C++";

$STR['plugin'][-1]="交互式";
$STR['plugin'][0]="<span style='color:#66FFFF; background-color:#005600'>评测插件</span>";
$STR['plugin'][1]="简单对比";
$STR['plugin'][2]="逐字节对比";

function getdescirbe($k)
{
	switch ($k)
	{
		case "data_server":	return "数据库服务器";
		case "data_database":	return "数据库名";
		case "data_uid":	return "数据库用户名";
		case "data_pwd":	return "数据库密码";
		case "dir_root":	return "网站根目录路径";
		case "safety_sn": return "安全安装许可";
		default: return "未知属性";
	}
}
?>
