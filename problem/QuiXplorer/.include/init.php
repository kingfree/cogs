<?php
/*------------------------------------------------------------------------------
     The contents of this file are subject to the Mozilla Public License
     Version 1.1 (the "License"); you may not use this file except in
     compliance with the License. You may obtain a copy of the License at
     http://www.mozilla.org/MPL/

     Software distributed under the License is distributed on an "AS IS"
     basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
     License for the specific language governing rights and limitations
     under the License.

     The Original Code is init.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.

     Alternatively, the contents of this file may be used under the terms
     of the GNU General Public License Version 2 or later (the "GPL"), in
     which case the provisions of the GPL are applicable instead of
     those above. If you wish to allow use of your version of this file only
     under the terms of the GPL and not to allow others to use
     your version of this file under the MPL, indicate your decision by
     deleting  the provisions above and replace  them with the notice and
     other provisions required by the GPL.  If you do not delete
     the provisions above, a recipient may use your version of this file
     under either the MPL or the GPL."
------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------
Author: The QuiX project
	quix@free.fr
	http://www.quix.tk
	http://quixplorer.sourceforge.net

Comment:
	QuiXplorer Version 2.3
	Main File
	
	Have Fun...
------------------------------------------------------------------------------*/
//------------------------------------------------------------------------------
// Vars
if(isset($_SERVER)) {
	$GLOBALS['__GET']	=&$_GET;
	$GLOBALS['__POST']	=&$_POST;
	$GLOBALS['__SERVER']	=&$_SERVER;
	$GLOBALS['__FILES']	=&$_FILES;
} elseif(isset($HTTP_SERVER_VARS)) {
	$GLOBALS['__GET']	=&$HTTP_GET_VARS;
	$GLOBALS['__POST']	=&$HTTP_POST_VARS;
	$GLOBALS['__SERVER']	=&$HTTP_SERVER_VARS;
	$GLOBALS['__FILES']	=&$HTTP_POST_FILES;
} else {
	die("<B>ERROR: Your PHP version is too old</B><BR>".
	"You need at least PHP 4.0.0 to run QuiXplorer; preferably PHP 4.3.1 or higher.");
}
//------------------------------------------------------------------------------
// Get Action
if(isset($GLOBALS['__GET']["action"])) $GLOBALS["action"]=$GLOBALS['__GET']["action"];
else $GLOBALS["action"]="list";
if($GLOBALS["action"]=="post" && isset($GLOBALS['__POST']["do_action"])) {
	$GLOBALS["action"]=$GLOBALS['__POST']["do_action"];
}
if($GLOBALS["action"]=="") $GLOBALS["action"]="list";
$GLOBALS["action"]=stripslashes($GLOBALS["action"]);
// Default Dir
if(isset($GLOBALS['__GET']["dir"])) $GLOBALS["dir"]=stripslashes($GLOBALS['__GET']["dir"]);
else $GLOBALS["dir"]="";
if($GLOBALS["dir"]==".") $GLOBALS["dir"]=="";
// Get Item
if(isset($GLOBALS['__GET']["item"])) $GLOBALS["item"]=stripslashes($GLOBALS['__GET']["item"]);
else $GLOBALS["item"]="";
// Get Sort
if(isset($GLOBALS['__GET']["order"])) $GLOBALS["order"]=stripslashes($GLOBALS['__GET']["order"]);
else $GLOBALS["order"]="name";
if($GLOBALS["order"]=="") $GLOBALS["order"]=="name";
// Get Sortorder (yes==up)
if(isset($GLOBALS['__GET']["srt"])) $GLOBALS["srt"]=stripslashes($GLOBALS['__GET']["srt"]);
else $GLOBALS["srt"]="yes";
if($GLOBALS["srt"]=="") $GLOBALS["srt"]=="yes";
// Get Language
if(isset($GLOBALS['__GET']["lang"])) $GLOBALS["lang"]=$GLOBALS['__GET']["lang"];
elseif(isset($GLOBALS['__POST']["lang"])) $GLOBALS["lang"]=$GLOBALS['__POST']["lang"];
//------------------------------------------------------------------------------
// Necessary files
ob_start(); // prevent unwanted output
require "./.config/conf.php";
if(isset($GLOBALS["lang"])) $GLOBALS["language"]=$GLOBALS["lang"];
require "./_lang/".$GLOBALS["language"].".php";
require "./_lang/".$GLOBALS["language"]."_mimes.php";
require "./.config/mimes.php";
require "./.include/fun_extra.php";
require "./.include/header.php";
require "./.include/footer.php";
require "./.include/error.php";
ob_end_clean(); // get rid of cached unwanted output
//------------------------------------------------------------------------------
if($GLOBALS["require_login"]) {	// LOGIN
	ob_start(); // prevent unwanted output
	require "./.include/login.php";
	ob_end_clean(); // get rid of cached unwanted output
	if($GLOBALS["action"]=="logout") {
		logout();
	} else {
		login();
	}
}
//------------------------------------------------------------------------------
$abs_dir=get_abs_dir($GLOBALS["dir"]);
if(!@file_exists($GLOBALS["home_dir"])) {
	if($GLOBALS["require_login"]) {
		$extra="<A HREF=\"".make_link("logout",NULL,NULL)."\">".
			$GLOBALS["messages"]["btnlogout"]."</A>";
	} else $extra=NULL;
	show_error($GLOBALS["error_msg"]["home"],$extra);
}
if(!down_home($abs_dir)) show_error($GLOBALS["dir"]." : ".$GLOBALS["error_msg"]["abovehome"]);
if(!is_dir($abs_dir)) show_error($GLOBALS["dir"]." : ".$GLOBALS["error_msg"]["direxist"]);
//------------------------------------------------------------------------------
?>
