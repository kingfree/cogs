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

     The Original Code is index.php, released on 2003-04-02.

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
Authors: quix@free.fr, ck@realtime-projects.com
	http://quixplorer.sourceforge.net

Comment:
	QuiXplorer Version 2.4.2
	Main File

	Have Fun...
------------------------------------------------------------------------------*/

umask(002); // Added to make created files/dirs group writable

require_once "qx.php";
require "./_include/init.php";	// Init

global $action;

_debug( "index.php: checking action $action" );

$current_dir = qx_request("dir", "");

switch($action)
{		// Execute action

// EDIT FILE
case "edit":
	require "./_include/fun_edit_editarea.php";
	edit_file($current_dir, $GLOBALS["item"]);
break;
//------------------------------------------------------------------------------
// DELETE FILE(S)/DIR(S)
case "delete":
	require "./_include/fun_del.php";
	del_items($current_dir);
break;
//------------------------------------------------------------------------------
// COPY/MOVE FILE(S)/DIR(S)
case "copy":	case "move":
	require "./_include/fun_copy_move.php";
	copy_move_items($current_dir);
break;
//------------------------------------------------------------------------------
// DOWNLOAD FILE
case "download":
	ob_start(); // prevent unwanted output
	require "./_include/fun_down.php";
	ob_end_clean(); // get rid of cached unwanted output
    global $item;
    _debug("download item: $current_dir/$item");
    if ($item == '' )
        show_error($GLOBALS["error_msg"]["miscselitems"]);
	download_item($current_dir, $item);
	ob_start(false); // prevent unwanted output
	exit;
break;
case "download_selected":
	ob_start(); // prevent unwanted output
	require "./_include/fun_down.php";
	ob_end_clean(); // get rid of cached unwanted output
	download_selected($current_dir);
	ob_start(false); // prevent unwanted output
	exit;
break;

/**
  The file upload function.

  The user may choose between 3 uploader, the default (simple) one,
  uploadify (which is flashed based and not https capable) and ajaxupload.
  */
case "upload":
	$uploader = isset($GLOBALS["uploader"]) ? $GLOBALS["uploader"] : "default";
    switch ($uploader)
    {
        case "uploadify":
            require "./_include/fun_up_uploadify.php";
            break;
        case "ajaxupload":
            require "./_include/fun_up_ajaxupload.php";
            break;
        case "default":
        default:
            require "./_include/fun_up.php";
            break;
    }
	upload_items($current_dir);
    break;

//------------------------------------------------------------------------------
// UNZIP ZIP FILE added by laurenceHR
case "unzip":
	require "./_include/fun_unzip.php";
	unzip_item($current_dir);
break;
//------------------------------------------------------------------------------
// CREATE DIR/FILE
case "mkitem":
	require "./_include/fun_mkitem.php";
	make_item($current_dir);
break;
//------------------------------------------------------------------------------
// CHMOD FILE/DIR
case "chmod":
	require "./_include/fun_chmod.php";
	chmod_item($current_dir, $GLOBALS["item"]);
break;
//------------------------------------------------------------------------------
// SEARCH FOR FILE(S)/DIR(S)
case "search":
	require "./_include/fun_search.php";
	search_items($current_dir);
break;
//------------------------------------------------------------------------------
// CREATE ARCHIVE
case "arch":
	require "./_include/fun_archive.php";
	archive_items($current_dir);
break;
//------------------------------------------------------------------------------
// USER-ADMINISTRATION
case "admin":
	require "./_include/fun_admin.php";
	show_admin($current_dir);
break;
case "login":
    _debug("doing login");
    login();
    require "./_include/fun_list.php";
    list_dir($current_dir);
    break;
case "logout":
    _debug("doing logout");
    logout();

// DEFAULT: LIST FILES & DIRS
case "list":
default:
	require "./_include/fun_list.php";
	list_dir($current_dir);
//------------------------------------------------------------------------------
}				// end switch-statement
//------------------------------------------------------------------------------
show_footer();
//------------------------------------------------------------------------------
?>
