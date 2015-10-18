<?php

// English Language Module for v2.3 (translated by the QuiX project)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y-m-d H:i";
$GLOBALS["error_msg"] = array(
	// error
	"error"			=> "ERROR(S)",
	"back"			=> "Go Back",
	
	// root
	"home"			=> "The home directory doesn't exist, check your settings.",
	"abovehome"		=> "The current directory may not be above the home directory.",
	"targetabovehome"	=> "The target directory may not be above the home directory.",
	
	// exist
	"direxist"		=> "This directory doesn't exist.",
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
	"permread"		=> "Getting permissions failed.",
	"permchange"		=> "Permission-change failed.",
	"openfile"		=> "File opening failed.",
	"savefile"		=> "File saving failed.",
	"createfile"		=> "File creation failed.",
	"createdir"		=> "Directory creation failed.",
	"uploadfile"		=> "File upload failed.",
	"copyitem"		=> "Copying failed.",
	"moveitem"		=> "Moving failed.",
	"delitem"		=> "Deleting failed.",
	"chpass"		=> "Changing password failed.",
	"login_failed"		=> "Login failed",
	"deluser"		=> "Removing user failed.",
	"adduser"		=> "Adding user failed.",
	"saveuser"		=> "Saving user failed.",
	"searchnothing"		=> "You must supply something to search for.",
	
	// misc
	"miscnofunc"		=> "Function unavailable.",
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
	"permlink"		=> "CHANGE PERMISSIONS",
	"editlink"		=> "EDIT",
	"downlink"		=> "DOWNLOAD",
	"download_selected"		=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "UP",
	"homelink"		=> "HOME",
	"reloadlink"		=> "RELOAD",
	"copylink"		=> "COPY",
	"movelink"		=> "MOVE",
	"dellink"		=> "DELETE",
	"comprlink"		=> "ARCHIVE",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "LOGOUT",
	"uploadlink"		=> "UPLOAD",
	"searchlink"		=> "SEARCH",
	"unziplink"			=> "UNZIP",
	
	// list
	"nameheader"		=> "Name",
	"sizeheader"		=> "Size",
	"typeheader"		=> "Type",
	"modifheader"		=> "Modified",
	"permheader"		=> "Perm's",
	"actionheader"		=> "Actions",
	"pathheader"		=> "Path",
	
	// buttons
	"btncancel"		=> "Cancel",
	"btnsave"		=> "Save",
	"btnchange"		=> "Change",
	"btnreset"		=> "Reset",
	"btnclose"		=> "Close",
	"btncreate"		=> "Create",
	"btnsearch"		=> "Search",
	"btnupload"		=> "Upload",
	"btncopy"		=> "Copy",
	"btnmove"		=> "Move",
	"btnlogin"		=> "Login",
	"btnlogout"		=> "Logout",
	"btnadd"		=> "Add",
	"btnedit"		=> "Edit",
	"btnremove"		=> "Remove",
	"btnunzip"		=> "Unzip",
	
	// actions
	"actdir"		=> "Directory",
	"actperms"		=> "Change permissions",
	"actedit"		=> "Edit file",
	"actsearchresults"	=> "Search results",
	"actcopyitems"		=> "Copy item(s)",
	"actcopyfrom"		=> "Copy from /%s to /%s ",
	"actmoveitems"		=> "Move item(s)",
	"actmovefrom"		=> "Move from /%s to /%s ",
	"actlogin"		=> "Login",
	"actloginheader"	=> "Login to use QuiXplorer",
	"actadmin"		=> "Administration",
	"actchpwd"		=> "Change password",
	"actusers"		=> "Users",
	"actarchive"		=> "Archive item(s)",
"actunzipitem"		=> "Extracting",
	"actupload"		=> "Upload file(s)",
	
	// misc
	"miscitems"		=> "Item(s)",
	"miscfree"		=> "Free",
	"miscusername"		=> "Username",
	"miscpassword"		=> "Password",
	"miscoldpass"		=> "Old password",
	"miscnewpass"		=> "New password",
	"miscconfpass"		=> "Confirm password",
	"miscconfnewpass"	=> "Confirm new password",
	"miscchpass"		=> "Change password",
	"mischomedir"		=> "Home directory",
	"mischomeurl"		=> "Home URL",
	"miscshowhidden"	=> "Show hidden items",
	"mischidepattern"	=> "Hide pattern",
	"miscperms"		=> "Permissions",
	"miscuseritems"		=> "(name, home directory, show hidden items, permissions, active)",
	"miscadduser"		=> "add user",
	"miscedituser"		=> "edit user '%s'",
	"miscactive"		=> "Active",
	"misclang"		=> "Language",
	"miscnoresult"		=> "No results available.",
	"miscsubdirs"		=> "Search subdirectories",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"		=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Yes","No","Y","N"),
	"miscchmod"		=> array("Owner", "Group", "Public"),

    "select_file"   => "select file",
    "uploading"     => "uploading",
);
?>
