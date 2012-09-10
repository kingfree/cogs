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

     The Original Code is fun_copy_move.php, released on 2003-03-31.

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
	File/Directory Copy & Move Functions
	
	Have Fun...
------------------------------------------------------------------------------*/
//------------------------------------------------------------------------------
function dir_list($dir) {			// make list of directories
	// this list is used to copy/move items to a specific location
	
	$handle = @opendir(get_abs_dir($dir));
	if($handle===false) return;		// unable to open dir
	
	while(($new_item=readdir($handle))!==false) {
		//if(!@file_exists(get_abs_item($dir, $new_item))) continue;
		
		if(!get_show_item($dir, $new_item)) continue;
		if(!get_is_dir($dir,$new_item)) continue;
		$dir_list[$new_item] = $new_item;
	}
	
	// sort
	if(is_array($dir_list)) ksort($dir_list);
	return $dir_list;
}
//------------------------------------------------------------------------------
function dir_print($dir_list, $new_dir) {	// print list of directories
	// this list is used to copy/move items to a specific location
	
	// Link to Parent Directory
	$dir_up = dirname($new_dir);
	if($dir_up==".") $dir_up = "";
	
	echo "<TR><TD><A HREF=\"javascript:NewDir('".$dir_up;
	echo "');\"><IMG border=\"0\" width=\"16\" height=\"16\"";
	echo " align=\"ABSMIDDLE\" src=\"_img/_up.gif\" ALT=\"\">&nbsp;..</A></TD></TR>\n";
	
	// Print List Of Target Directories
	if(!is_array($dir_list)) return;
	while(list($new_item,) = each($dir_list)) {
		$s_item=$new_item;	if(strlen($s_item)>40) $s_item=substr($s_item,0,37)."...";
		echo "<TR><TD><A HREF=\"javascript:NewDir('".get_rel_item($new_dir,$new_item).
			"');\"><IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" ".
			"src=\"_img/dir.gif\" ALT=\"\">&nbsp;".$s_item."</A></TD></TR>\n";
	}
}
//------------------------------------------------------------------------------
function copy_move_items($dir) {		// copy/move file/dir
	if(($GLOBALS["permissions"]&01)!=01) show_error($GLOBALS["error_msg"]["accessfunc"]);
	
	// Vars
	$first = $GLOBALS['__POST']["first"];
	if($first=="y") $new_dir=$dir;
	else $new_dir = stripslashes($GLOBALS['__POST']["new_dir"]);
	if($new_dir==".") $new_dir="";
	$cnt=count($GLOBALS['__POST']["selitems"]);

	// Copy or Move?
	if($GLOBALS["action"]!="move") {
		$_img="_img/__copy.gif";
	} else {
		$_img="_img/__cut.gif";
	}
	
	// Get New Location & Names
	if(!isset($GLOBALS['__POST']["confirm"]) || $GLOBALS['__POST']["confirm"]!="true") {
		show_header(($GLOBALS["action"]!="move"?
			$GLOBALS["messages"]["actcopyitems"]:
			$GLOBALS["messages"]["actmoveitems"]
		));
		
		// JavaScript for Form:
		// Select new target directory / execute action
?><script language="JavaScript1.2" type="text/javascript">
<!--
	function NewDir(newdir) {
		document.selform.new_dir.value = newdir;
		document.selform.submit();
	}
	
	function Execute() {
		document.selform.confirm.value = "true";
	}
//-->
</script><?php
		
		// "Copy / Move from .. to .."
		$s_dir=$dir;		if(strlen($s_dir)>40) $s_dir="...".substr($s_dir,-37);
		$s_ndir=$new_dir;	if(strlen($s_ndir)>40) $s_ndir="...".substr($s_ndir,-37);
		echo "<BR><IMG SRC=\"".$_img."\" align=\"ABSMIDDLE\" ALT=\"\">&nbsp;";
		echo sprintf(($GLOBALS["action"]!="move"?$GLOBALS["messages"]["actcopyfrom"]:
			$GLOBALS["messages"]["actmovefrom"]),$s_dir, $s_ndir);
		echo "<IMG SRC=\"_img/__paste.gif\" align=\"ABSMIDDLE\" ALT=\"\">\n";
		
		// Form for Target Directory & New Names
		echo "<BR><BR><FORM name=\"selform\" method=\"post\" action=\"";
		echo make_link("post",$dir,NULL)."\"><TABLE>\n";
		echo "<INPUT type=\"hidden\" name=\"do_action\" value=\"".$GLOBALS["action"]."\">\n";
		echo "<INPUT type=\"hidden\" name=\"confirm\" value=\"false\">\n";
		echo "<INPUT type=\"hidden\" name=\"first\" value=\"n\">\n";
		echo "<INPUT type=\"hidden\" name=\"new_dir\" value=\"".$new_dir."\">\n";
		
		// List Directories to select Target
		dir_print(dir_list($new_dir),$new_dir);
		echo "</TABLE><BR><TABLE>\n";
		
		// Print Text Inputs to change Names
		for($i=0;$i<$cnt;++$i) {
			$selitem=stripslashes($GLOBALS['__POST']["selitems"][$i]);
			if(isset($GLOBALS['__POST']["newitems"][$i])) {
				$newitem=stripslashes($GLOBALS['__POST']["newitems"][$i]);
				if($first=="y") $newitem=$selitem;
			} else $newitem=$selitem;
			$s_item=$selitem;	if(strlen($s_item)>50) $s_item=substr($s_item,0,47)."...";
			echo "<TR><TD><IMG SRC=\"_img/_info.gif\" align=\"ABSMIDDLE\" ALT=\"\">";
			// Old Name
			echo "<INPUT type=\"hidden\" name=\"selitems[]\" value=\"";
			echo $selitem."\">&nbsp;".$s_item."&nbsp;";
			// New Name
			echo "</TD><TD><INPUT type=\"text\" size=\"25\" name=\"newitems[]\" value=\"";
			echo $newitem."\"></TD></TR>\n";
		}
		
		// Submit & Cancel
		echo "</TABLE><BR><TABLE><TR>\n<TD>";
		echo "<INPUT type=\"submit\" value=\"";
		echo ($GLOBALS["action"]!="move"?$GLOBALS["messages"]["btncopy"]:$GLOBALS["messages"]["btnmove"]);
		echo "\" onclick=\"javascript:Execute();\"></TD>\n<TD>";
		echo "<input type=\"button\" value=\"".$GLOBALS["messages"]["btncancel"];
		echo "\" onClick=\"javascript:location='".make_link("list",$dir,NULL);
		echo "';\"></TD>\n</TR></FORM></TABLE><BR>\n";
		return;
	}
	
	
	// DO COPY/MOVE
	
	// ALL OK?
	if(!@file_exists(get_abs_dir($new_dir))) show_error($new_dir.": ".$GLOBALS["error_msg"]["targetexist"]);
	if(!get_show_item($new_dir,"")) show_error($new_dir.": ".$GLOBALS["error_msg"]["accesstarget"]);
	if(!down_home(get_abs_dir($new_dir))) show_error($new_dir.": ".$GLOBALS["error_msg"]["targetabovehome"]);
	
	
	// copy / move files
	$err=false;
	for($i=0;$i<$cnt;++$i) {
		$tmp = stripslashes($GLOBALS['__POST']["selitems"][$i]);
		$new = basename(stripslashes($GLOBALS['__POST']["newitems"][$i]));
		$abs_item = get_abs_item($dir,$tmp);
		$abs_new_item = get_abs_item($new_dir,$new);
		$items[$i] = $tmp;
	
		// Check
		if($new=="") {
			$error[$i]= $GLOBALS["error_msg"]["miscnoname"];
			$err=true;	continue;
		}
		if(!@file_exists($abs_item)) {
			$error[$i]= $GLOBALS["error_msg"]["itemexist"];
			$err=true;	continue;
		}
		if(!get_show_item($dir, $tmp)) {
			$error[$i]= $GLOBALS["error_msg"]["accessitem"];
			$err=true;	continue;
		}
		if(@file_exists($abs_new_item)) {
			$error[$i]= $GLOBALS["error_msg"]["targetdoesexist"];
			$err=true;	continue;
		}
	
		// Copy / Move
		if($GLOBALS["action"]=="copy") {
			if(@is_link($abs_item) || @is_file($abs_item)) {
				// check file-exists to avoid error with 0-size files (PHP 4.3.0)
				$ok=@copy($abs_item,$abs_new_item);	//||@file_exists($abs_new_item);
			} elseif(@is_dir($abs_item)) {
				$ok=copy_dir($abs_item,$abs_new_item);
			}
		} else {
			$ok=@rename($abs_item,$abs_new_item);
		}
		
		if($ok===false) {
			$error[$i]=($GLOBALS["action"]=="copy"?
				$GLOBALS["error_msg"]["copyitem"]:
				$GLOBALS["error_msg"]["moveitem"]
			);
			$err=true;	continue;
		}
		
		$error[$i]=NULL;
	}
	
	if($err) {			// there were errors
		$err_msg="";
		for($i=0;$i<$cnt;++$i) {
			if($error[$i]==NULL) continue;
			
			$err_msg .= $items[$i]." : ".$error[$i]."<BR>\n";
		}
		show_error($err_msg);
	}
	
	header("Location: ".make_link("list",$dir,NULL));
}
//------------------------------------------------------------------------------
?>
