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

     The Original Code is fun_up.php, released on 2003-03-31.

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
	File-Upload Functions
	
	Have Fun...
------------------------------------------------------------------------------*/
require_once("./.include/permissions.php");
//------------------------------------------------------------------------------
// upload file
function upload_items($dir)
{
	if (!permissions_grant($dir, NULL, "create"))
		show_error($GLOBALS["error_msg"]["accessfunc"]);
	
	// Execute
	if(isset($GLOBALS['__POST']["confirm"]) && $GLOBALS['__POST']["confirm"]=="true") {	
		$cnt=count($GLOBALS['__FILES']['userfile']['name']);
		$err=false;
		$err_avaliable=isset($GLOBALS['__FILES']['userfile']['error']);
	
		// upload files & check for errors
		for($i=0;$i<$cnt;$i++) {
			$errors[$i]=NULL;
			$tmp = $GLOBALS['__FILES']['userfile']['tmp_name'][$i];
			$items[$i] = stripslashes($GLOBALS['__FILES']['userfile']['name'][$i]);
			if($err_avaliable) $up_err = $GLOBALS['__FILES']['userfile']['error'][$i];
			else $up_err=(file_exists($tmp)?0:4);
			$abs = get_abs_item($dir,$items[$i]);
		
			if($items[$i]=="" || $up_err==4) continue;
			if($up_err==1 || $up_err==2) {
				$errors[$i]=$GLOBALS["error_msg"]["miscfilesize"];
				$err=true;	continue;
			}
			if($up_err==3) {
				$errors[$i]=$GLOBALS["error_msg"]["miscfilepart"];
				$err=true;	continue;
			}
			if(!@is_uploaded_file($tmp)) {
				$errors[$i]=$GLOBALS["error_msg"]["uploadfile"];
				$err=true;	continue;
			}
			if(@file_exists($abs)) {
				$errors[$i]=$GLOBALS["error_msg"]["itemdoesexist"];
				$err=true;	continue;
			}
			
			// Upload
			if(function_exists("move_uploaded_file")) {
				$ok = @move_uploaded_file($tmp, $abs);
			} else {
				$ok = @copy($tmp, $abs);
				@nlink($tmp);	// try to delete...
			}
			
			if($ok===false) {
				$errors[$i]=$GLOBALS["error_msg"]["uploadfile"];
				$err=true;	continue;
			}
		}
		
		if($err) {			// there were errors
			$err_msg="";
			for($i=0;$i<$cnt;$i++) {
				if($errors[$i]==NULL) continue;
				$err_msg .= $items[$i]." : ".$errors[$i]."<BR>\n";
			}
			show_error($err_msg);
		}
		
		header("Location: ".make_link("list",$dir,NULL));
		return;
	}
	
	show_header($GLOBALS["messages"]["actupload"]);
	
?>

<script type="text/javascript"> 

// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").pluploadQueue({
		// General settings
		runtimes : 'gears,flash,silverlight,browserplus,html5',
		url : '<?php echo make_link("upload",$dir,NULL);?>',
		max_file_size : '<?echo get_max_file_size();?>',
		chunk_size : '1mb',
		unique_names : true,

		// Resize images on clientside if we can
		resize : {width : 320, height : 240, quality : 90},

		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip"}
		],

		// Flash settings
		flash_swf_url : '/apps/plupload/js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : '/apps/plupload/js/plupload.silverlight.xap'
	});

	// Client side form validation
	$('form').submit(function(e) {
		var uploader = $('#uploader').pluploadQueue();

		// Validate number of uploaded files
		if (uploader.total.uploaded == 0) {
			// Files in queue upload them first
			if (uploader.files.length > 0) {
				// When all files are uploaded submit form
				uploader.bind('UploadProgress', function() {
					if (uploader.total.uploaded == uploader.files.length)
						$('form').submit();
				});

				uploader.start();
			} else
				alert('You must at least upload one file.');

			e.preventDefault();
		}
	});
});
</script> 

<?php	
	// List
	echo "<BR><FORM enctype=\"multipart/form-data\" action=\"".make_link("upload",$dir,NULL);
	echo "\" method=\"post\">\n";

		echo "<TR><TD nowrap align=\"center\">\n";
		echo "<div id=\"uploader\" style=\"height: 330px;\">\n";
		echo "	<p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>\n";
		echo "</div>\n";

		echo "</TD></TR>\n";
	
	//echo "<input id=\"file_upload\" name=\"file_upload\" type=\"file\" />\n";

	echo "</TABLE>\n<BR><TABLE><TR><TD><INPUT type=\"submit\" value=\"Listo\"";
	echo "\"></TD></TR></FORM></TABLE><BR>\n";
//	echo "\"></TD>\n<TD><input type=\"button\" value=\"".$GLOBALS["messages"]["btncancel"];
//	echo "\" onClick=\"javascript:location='".make_link("list",$dir,NULL)."';\">\n</TD></TR></FORM></TABLE><BR>\n";
	
	return;
}
//------------------------------------------------------------------------------
?>
