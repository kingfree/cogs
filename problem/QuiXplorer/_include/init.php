<?php

require_once "_include/error.php";

_debug("Initializing ---------------------------------------------------");
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

_debug("xxx3 action: " . $GLOBALS['__GET']["action"] . "/" . $GLOBALS["__GET"]["do_action"] . "/" . (isset($GLOBALS['__GET']['action']) ? "true" : "false"));
if (isset($GLOBALS['__GET']["action"]))
{
    $GLOBALS["action"]=$GLOBALS['__GET']["action"];
}
else
{
    $GLOBALS["action"]="list";
}
if($GLOBALS["action"]=="post" && isset($GLOBALS['__POST']["do_action"])) {
	$GLOBALS["action"]=$GLOBALS['__POST']["do_action"];
}
if($GLOBALS["action"]=="") $GLOBALS["action"]="list";
$GLOBALS["action"]=stripslashes($GLOBALS["action"]);
_debug("xxx3 action: " . $GLOBALS['__GET']["action"] . "/" . $GLOBALS["__GET"]["do_action"] . "/" . (isset($GLOBALS['__GET']['action']) ? "true" : "false"));


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

// Necessary files
ob_start(); // prevent unwanted output
date_default_timezone_set ( "UTC" );
if (!is_readable("./_config/conf.php"))
    show_error("./_config/conf.php not found.. please see installation instructions");

require "./_config/conf.php";
require "./_config/configs.php";

_load_language($GLOBALS['language']);

require "./_config/mimes.php";
require "./_include/fun_extra.php";
require_once "./_include/header.php";
require "./_include/footer.php";
ob_start(); // prevent unwanted output
require_once "./_include/login.php";

login_check();

// after login, language may have changed..
if (isset($_SESSION["language"]))
{
    _load_language($_SESSION['language']);
}

ob_end_clean(); // get rid of cached unwanted output
$prompt = isset($GLOBALS["login_prompt"][$GLOBALS["language"]])
	? $GLOBALS["login_prompt"][$GLOBALS["language"]]
	: $GLOBALS["login_prompt"]["en"];
if (isset($prompt))
	$GLOBALS["messages"]["actloginheader"] = $prompt;

ob_end_clean(); // get rid of cached unwanted output

function _load_language($lang)
{
    if (!isset($lang))
        $lang = 'en';
    require "./_lang/$lang.php";
    require "./_lang/$lang" . "_mimes.php";
}
?>
