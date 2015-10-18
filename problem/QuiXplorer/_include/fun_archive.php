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

     The Original Code is fun_archive.php, released on 2003-03-31.

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
	Zip, Tar & Gzip Functions

	Have Fun...
------------------------------------------------------------------------------*/
//------------------------------------------------------------------------------
//if($GLOBALS["tar"]) include("./_lib/lib_tar.php");
//if($GLOBALS["tgz"]) include("./_lib/lib_tgz.php");
//

require_once("qxpage.php");
require_once("_lib/zipstream.php");

/**
 * _zip
 * @return void
 **/
function zip_selected_items($zipfilename, $directory, $items)
{
    $zipfile=new ZipArchive();
    $zipfile->open($zipfilename, ZIPARCHIVE::CREATE);
    foreach ($items as $item)
    {
        $srcfile = $directory . DIRECTORY_SEPARATOR . $item;
        if (!$zipfile->addFile($srcfile, $item))
        {
            show_error($srcfile . ": Failed adding item.");
        }
    }

    if (!$zipfile->close())
    {
      show_error($zipfilename . ": Failed saving zipfile.");
    }
}

function zip_items($dir, $name)
{
    $items = qxpage_selected_items();
    if (!preg_match("/\.zip$/", $name))
    {
        $name .= ".zip";
    }
    zip_selected_items(get_abs_item($dir, $name), $dir, $items);
	header("Location: " . make_link("list",$dir,NULL));
}

function zip_download($directory, $items)
{
    $zipfile = new ZipStream("downloads.zip");
    foreach ($items as $item)
    {
        _zipstream_add_file($zipfile, $directory, $item);
    }
    $zipfile->finish();
}

function _zipstream_add_file($zipfile, $directory, $file_to_add)
{
    $filename = $directory.DIRECTORY_SEPARATOR.$file_to_add;

    if (!@file_exists($filename))
    {
        show_error($filename." does not exist");
    }

    if (is_file($filename))
    {
        _debug("adding file $filename");
        return $zipfile->add_file($file_to_add, file_get_contents($filename));
    }

    if (is_dir($filename))
    {
        _debug("adding directory $filename");
        $files = glob($filename.DIRECTORY_SEPARATOR."*");
        foreach ($files as $file)
        {
            $file = str_replace($directory.DIRECTORY_SEPARATOR, "", $file);
            _zipstream_add_file($zipfile, $directory, $file);
        }
        return True;
    }

    _error("don't know how to handle $file_to_add");
    return False;
}

function tar_items($dir,$name) {
	// ...
}
//------------------------------------------------------------------------------
function tgz_items($dir,$name) {
	// ...
}
//------------------------------------------------------------------------------
function archive_items($dir)
{
        include_once "./_include/permissions.php";
	// archive is only allowed if user may change files
	if (!permissions_grant($dir, NULL, "change"))
		show_error($GLOBALS["error_msg"]["accessfunc"]);

	if(!$GLOBALS["zip"] && !$GLOBALS["tar"] && !$GLOBALS["tgz"]) show_error($GLOBALS["error_msg"]["miscnofunc"]);

	if(isset($GLOBALS['__POST']["name"])) {
		$name=basename(stripslashes($GLOBALS['__POST']["name"]));
		if($name=="") show_error($GLOBALS["error_msg"]["miscnoname"]);
		switch($GLOBALS['__POST']["type"]) {
			case "zip":	zip_items($dir,$name);	break;
			case "tar":	tar_items($dir,$name);	break;
			default:		tgz_items($dir,$name);
		}
		header("Location: ".make_link("list",$dir,NULL));
	}

	show_header($GLOBALS["messages"]["actarchive"]);
	echo "<BR><FORM name=\"archform\" method=\"post\" action=\"".make_link("arch",$dir,NULL)."\">\n";

	$cnt=count($GLOBALS['__POST']["selitems"]);
	for($i=0;$i<$cnt;++$i) {
		echo "<INPUT type=\"hidden\" name=\"selitems[]\" value=\"".stripslashes($GLOBALS['__POST']["selitems"][$i])."\">\n";
	}

	echo "<TABLE width=\"300\"><TR><TD>".$GLOBALS["messages"]["nameheader"].":</TD><TD align=\"right\">";
	echo "<INPUT type=\"text\" name=\"name\" size=\"25\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["typeheader"].":</TD><TD align=\"right\"><SELECT name=\"type\">\n";
	if($GLOBALS["zip"]) echo "<OPTION value=\"zip\">Zip</OPTION>\n";
	if($GLOBALS["tar"]) echo "<OPTION value=\"tar\">Tar</OPTION>\n";
	if($GLOBALS["tgz"]) echo "<OPTION value=\"tgz\">TGz</OPTION>\n";
	echo "</SELECT></TD></TR>";
	echo "<TR><TD></TD><TD align=\"right\"><INPUT type=\"submit\" value=\"".$GLOBALS["messages"]["btncreate"]."\">\n";
	echo "<input type=\"button\" value=\"".$GLOBALS["messages"]["btncancel"];
	echo "\" onClick=\"javascript:location='".make_link("list",$dir,NULL)."';\">\n</TD></TR></FORM></TABLE><BR>\n";
?><script language="JavaScript1.2" type="text/javascript">
<!--
	if(document.archform) document.archform.name.focus();
// -->
</script><?php
}
//------------------------------------------------------------------------------
?>
