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
umask(002); // Added to make created files/dirs group writable
//------------------------------------------------------------------------------
require "./.include/init.php";	// Init
//------------------------------------------------------------------------------
switch($GLOBALS["action"]) {		// Execute action
//------------------------------------------------------------------------------
// EDIT FILE
case "edit":
	require "./.include/fun_edit.php";
	edit_file($GLOBALS["dir"], $GLOBALS["item"]);
break;
//------------------------------------------------------------------------------
// DELETE FILE(S)/DIR(S)
case "delete":
	require "./.include/fun_del.php";
	del_items($GLOBALS["dir"]);
break;
//------------------------------------------------------------------------------
// COPY/MOVE FILE(S)/DIR(S)
case "copy":	case "move":
	require "./.include/fun_copy_move.php";
	copy_move_items($GLOBALS["dir"]);
break;
//------------------------------------------------------------------------------
// DOWNLOAD FILE
case "download":
	ob_start(); // prevent unwanted output
	require "./.include/fun_down.php";
	ob_end_clean(); // get rid of cached unwanted output
	download_item($GLOBALS["dir"], $GLOBALS["item"]);
	ob_start(false); // prevent unwanted output
	exit;
break;
//------------------------------------------------------------------------------
// UPLOAD FILE(S)
case "upload":
	require "./.include/fun_up.php";
	upload_items($GLOBALS["dir"]);
break;
//------------------------------------------------------------------------------
// CREATE DIR/FILE
case "mkitem":
	require "./.include/fun_mkitem.php";
	make_item($GLOBALS["dir"]);
break;
//------------------------------------------------------------------------------
// CHMOD FILE/DIR
case "chmod":
	require "./.include/fun_chmod.php";
	chmod_item($GLOBALS["dir"], $GLOBALS["item"]);
break;
//------------------------------------------------------------------------------
// SEARCH FOR FILE(S)/DIR(S)
case "search":
	require "./.include/fun_search.php";
	search_items($GLOBALS["dir"]);
break;
//------------------------------------------------------------------------------
// CREATE ARCHIVE
case "arch":
	require "./.include/fun_archive.php";
	archive_items($GLOBALS["dir"]);
break;
//------------------------------------------------------------------------------
// USER-ADMINISTRATION
case "admin":
	require "./.include/fun_admin.php";
	show_admin($GLOBALS["dir"]);
break;
//------------------------------------------------------------------------------
// DEFAULT: LIST FILES & DIRS
case "list":
default:
	require "./.include/fun_list.php";
	list_dir($GLOBALS["dir"]);
//------------------------------------------------------------------------------
}				// end switch-statement
//------------------------------------------------------------------------------
?>
