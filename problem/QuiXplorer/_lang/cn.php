<?php

// Chinese Language Module for v2.3 (translated by Kingfree)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y-m-d H:i";
$GLOBALS["error_msg"] = array(
	// error
	"error"			=> "错误",
	"back"			=> "返回",
	
	// root
	"home"			=> "The home directory doesn't exist, check your settings.",
	"abovehome"		=> "The current directory may not be above the home directory.",
	"targetabovehome"	=> "The target directory may not be above the home directory.",
	
	// exist
	"direxist"		=> "This directory doesn't exist.",
	//"filedoesexist"	=> "This file already exists.",
	"fileexist"		=> "This file doesn't exist.",
	"itemdoesexist"		=> "This item already exists.",
	"itemexist"		=> "This item doesn't exist.",
	"targetexist"		=> "The target directory doesn't exist.",
	"targetdoesexist"	=> "The target item already exists.",
	
	// open
	"opendir"		=> "Unable to open directory.",
	"readdir"		=> "Unable to read directory.",
	
	// access
	"accessdir"		=> "You are not allowed to access this directory.",
	"accessfile"		=> "You are not allowed to access this file.",
	"accessitem"		=> "You are not allowed to access this item.",
	"accessfunc"		=> "You are not allowed to use this function.",
	"accesstarget"		=> "You are not allowed to access the target directory.",
	
	// actions
	"permread"		=> "Getting permissions 失败。",
	"permchange"		=> "Permission-change 失败。",
	"openfile"		=> "File opening 失败。",
	"savefile"		=> "File saving 失败。",
	"createfile"		=> "File creation 失败。",
	"createdir"		=> "Directory creation 失败。",
	"uploadfile"		=> "File upload 失败。",
	"copyitem"		=> "Copying 失败。",
	"moveitem"		=> "Moving 失败。",
	"delitem"		=> "Deleting 失败。",
	"chpass"		=> "Changing password 失败。",
	"deluser"		=> "Removing user 失败。",
	"adduser"		=> "Adding user 失败。",
	"saveuser"		=> "Saving user 失败。",
	"searchnothing"		=> "You must supply something to search for.",
	
	// misc
	"miscnofunc"		=> "函数不可用。",
	"miscfilesize"		=> "File exceeds maximum size.",
	"miscfilepart"		=> "File was only partially uploaded.",
	"miscnoname"		=> "You must supply a name.",
	"miscselitems"		=> "You haven't selected any item(s).",
	"miscdelitems"		=> "Are you sure you want to delete these \"+num+\" item(s)?",
	"miscdeluser"		=> "Are you sure you want to delete user '\"+user+\"'?",
	"miscnopassdiff"	=> "New password doesn't differ from current.",
	"miscnopassmatch"	=> "Passwords don't match.",
	"miscfieldmissed"	=> "You missed an important field.",
	"miscnouserpass"	=> "Username or password incorrect.",
	"miscselfremove"	=> "You can't remove yourself.",
	"miscuserexist"		=> "User already exists.",
	"miscnofinduser"	=> "Can't find user.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "更改权限",
	"editlink"		=> "编辑",
	"downlink"		=> "下载",
	"uplink"		=> "向上",
	"homelink"		=> "主目录",
	"reloadlink"		=> "刷新",
	"copylink"		=> "复制",
	"movelink"		=> "移动",
	"dellink"		=> "删除",
	"comprlink"		=> "压缩",
	"adminlink"		=> "管理",
	"logoutlink"		=> "注销",
	"uploadlink"		=> "上传",
	"searchlink"		=> "搜索",
	
	// list
	"nameheader"		=> "名称",
	"sizeheader"		=> "大小",
	"typeheader"		=> "类型",
	"modifheader"		=> "修改时间",
	"permheader"		=> "权限列表",
	"actionheader"		=> "操作",
	"pathheader"		=> "路径",
	
	// buttons
	"btncancel"		=> "取消",
	"btnsave"		=> "保存",
	"btnchange"		=> "更改",
	"btnreset"		=> "重置",
	"btnclose"		=> "关闭",
	"btncreate"		=> "创建",
	"btnsearch"		=> "搜索",
	"btnupload"		=> "上传",
	"btncopy"		=> "复制",
	"btnmove"		=> "移动",
	"btnlogin"		=> "登录",
	"btnlogout"		=> "注销",
	"btnadd"		=> "添加",
	"btnedit"		=> "编辑",
	"btnremove"		=> "移除",
	
	// actions
	"actdir"		=> "目录",
	"actperms"		=> "更改权限",
	"actedit"		=> "编辑文件",
	"actsearchresults"	=> "搜索结果",
	"actcopyitems"		=> "Copy item(s)",
	"actcopyfrom"		=> "Copy from /%s to /%s ",
	"actmoveitems"		=> "Move item(s)",
	"actmovefrom"		=> "Move from /%s to /%s ",
	"actlogin"		=> "登录",
	"actloginheader"	=> "登录使用 QuiXplorer",
	"actadmin"		=> "管理",
	"actchpwd"		=> "修改密码",
	"actusers"		=> "用户",
	"actarchive"		=> "压缩文件",
	"actupload"		=> "上传文件",
	
	// misc
	"miscitems"		=> "项目",
	"miscfree"		=> "Free",
	"miscusername"		=> "用户",
	"miscpassword"		=> "密码",
	"miscoldpass"		=> "旧密码",
	"miscnewpass"		=> "新密码",
	"miscconfpass"		=> "确认密码",
	"miscconfnewpass"	=> "确认新密码",
	"miscchpass"		=> "修改密码",
	"mischomedir"		=> "主目录",
	"mischomeurl"		=> "主目录 URL",
	"miscshowhidden"	=> "显示隐藏项目",
	"mischidepattern"	=> "隐藏选项",
	"miscperms"		=> "权限列表",
	"miscuseritems"		=> "(name, home directory, show hidden items, permissions, active)",
	"miscadduser"		=> "add user",
	"miscedituser"		=> "edit user '%s'",
	"miscactive"		=> "操作",
	"misclang"		=> "语言",
	"miscnoresult"		=> "没有可用结果。",
	"miscsubdirs"		=> "搜索子目录",
	"miscpermnames"		=> array("只能浏览","编辑文件","修改密码","编辑文件和修改密码","管理员"),
	"miscyesno"		=> array("是","否","Y","N"),
	"miscchmod"		=> array("Owner", "Group", "Public"),
);
?>
