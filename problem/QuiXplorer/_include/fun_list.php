<?php

require_once("./_include/permissions.php");
require_once("./_include/login.php");
require_once("./_include/qxpath.php");

function make_list($_list1, $_list2) {		// make list of files
	$list = array();

	if($GLOBALS["srt"]=="yes") {
		$list1 = $_list1;
		$list2 = $_list2;
	} else {
		$list1 = $_list2;
		$list2 = $_list1;
	}

	if(is_array($list1)) {
		while (list($key, $val) = each($list1)) {
			$list[$key] = $val;
		}
	}

	if(is_array($list2)) {
		while (list($key, $val) = each($list2)) {
			$list[$key] = $val;
		}
	}

	return $list;
}
/**
 make table of files in dir
 make tables & place results in reference-variables passed to function
 also 'return' total filesize & total number of items
*/
function make_tables($dir, &$dir_list, &$file_list, &$tot_file_size, &$num_items)
{
    $tot_file_size = $num_items = 0;

    // Open directory
    $handle = @opendir(get_abs_dir($dir));
    if ($handle === false)
        show_error($dir . ": " . $GLOBALS["error_msg"]["opendir"]);

    // Read directory
    while (($new_item = readdir($handle)) !== false)
    {
        $abs_new_item = get_abs_item($dir, $new_item);

        if (!get_show_item($dir, $new_item))
            continue;

        $new_file_size = is_link($abs_new_item) ? 0 : @filesize($abs_new_item);
        $tot_file_size += $new_file_size;
        $num_items++;

        if (is_dir($dir.DIRECTORY_SEPARATOR.$new_item))
        {
            if ($GLOBALS["order"] == "mod")
            {
                $dir_list[$new_item] = @filemtime($abs_new_item);
            } else
            {
                // order == "size", "type" or "name"
                $dir_list[$new_item] = $new_item;
            }
        } else {
            if ($GLOBALS["order"] == "size")
            {
                $file_list[$new_item] = $new_file_size;
            }
            elseif ($GLOBALS["order"] == "mod")
            {
                $file_list[$new_item] = @filemtime($abs_new_item);
            }
            elseif ($GLOBALS["order"] == "type")
            {
                $file_list[$new_item] = get_mime_type($dir, $new_item, "type");
            }
            else
            {
                // order == "name"
                $file_list[$new_item] = $new_item;
            }
        }
    }
    closedir($handle);

    // sort
    if (is_array($dir_list))
    {
        if ($GLOBALS["order"]=="mod")
        {
            if ($GLOBALS["srt"] == "yes")
                arsort($dir_list);
            else
                asort($dir_list);
        } else
        {
            // order == "size", "type" or "name"
            if ($GLOBALS["srt"] == "yes")
                ksort($dir_list);
            else
                krsort($dir_list);
        }
    }

    // sort
    if (is_array($file_list))
    {
        if ($GLOBALS["order"] == "mod")
        {
            if ($GLOBALS["srt"] == "yes")
                arsort($file_list);
            else
                asort($file_list);
        } elseif ($GLOBALS["order"] == "size" || $GLOBALS["order"]=="type")
        {
            if ($GLOBALS["srt"] == "yes")
                asort($file_list);
            else
                arsort($file_list);
        } else {
            // order == "name"
            if ($GLOBALS["srt"] == "yes")
                ksort($file_list);
            else
                krsort($file_list);
        }
    }
}

/**
  print table of files
 */
function print_table ($dir, $list)
{
	if (!is_array($list))
        return;

	while (list($item) = each($list))
    {
		// link to dir / file
		$abs_item = get_abs_item($dir,$item);
		$target="";
		if (is_dir($abs_item))
        {
			$link = make_link("list", get_rel_item($dir, $item), NULL);
		} else {
			$link = make_link("download", $dir, $item);
			$target = "_blank";
		}

		echo "<TR class=\"rowdata\"><TD><INPUT TYPE=\"checkbox\" name=\"selitems[]\" value=\"";
		echo htmlspecialchars($item)."\" onclick=\"javascript:Toggle(this);\"></TD>\n";
	// Icon + Link
		echo "<TD nowrap>";
		if (permissions_grant($dir, $item, "read"))
			echo"<A HREF=\"" . $link . "\">";
		//else echo "<A>";
		echo "<IMG border=\"0\" width=\"16\" height=\"16\" ";
		echo "align=\"ABSMIDDLE\" src=\"_img/".get_mime_type($dir, $item, "img")."\" ALT=\"\">&nbsp;";
		$s_item=$item;	if(strlen($s_item)>50) $s_item=substr($s_item,0,47)."...";
		echo htmlspecialchars($s_item);
		if (permissions_grant($dir, $item, "read"))
			echo "</A>";
		echo "</TD>\n";	// ...$extra...
	// Size
		echo '<TD>'.parse_file_size(get_file_size($dir,$item)) .sprintf("%10s","&nbsp;")."</TD>\n";
	// Type
		echo "<td>"._get_link_info($dir, $item, "type")."</td>\n";
	// Modified
		echo "<TD>".parse_file_date(get_file_date($dir,$item))."</TD>\n";
	// Permissions
		echo "<TD>";
		if (permissions_grant($dir, NULL, "change"))
		{
			echo "<A HREF=\"".make_link("chmod",$dir,$item)."\" TITLE=\"";
			echo $GLOBALS["messages"]["permlink"]."\">";
		}
		echo parse_file_type($dir,$item).parse_file_perms(get_file_perms($dir,$item));
		if (permissions_grant($dir, NULL, "change"))
			echo "</A>";
		echo "</TD>\n";
	// Actions
		echo "<TD>\n<TABLE>\n";
		// EDIT
		if(get_is_editable($dir, $item))
		{
			_print_link("edit", permissions_grant($dir, $item, "change"), $dir, $item);
		} else {
			// UNZIP
			if(get_is_unzipable($dir, $item))
			{
				_print_link("unzip", permissions_grant($dir, $item, "create"), $dir, $item);
			}else{
				echo "<TD><IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" ";
				echo "src=\"".$GLOBALS["baricons"]["none"]."\" ALT=\"\"></TD>\n";
			}
		}



		// DOWNLOAD
		if(get_is_file($dir,$item))
		{
			_print_link("download", permissions_grant($dir, $item, "read"), $dir, $item);
		} else {
			echo "<TD><IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" ";
			echo "src=\"".$GLOBALS["baricons"]["none"]."\" ALT=\"\"></TD>\n";
		}
		echo "</TABLE>\n</TD></TR>\n";
	}
}

/**
 MAIN FUNCTION
 */
function list_dir ( $dir )
{
    _debug("list_dir: displaying directory $dir");

	if ( ! get_show_item( $dir, NULL ) )
       show_error($GLOBALS["error_msg"]["accessdir"] . " : '$dir'");

	// make file & dir tables, & get total filesize & number of items
	make_tables($dir, $dir_list, $file_list, $tot_file_size, $num_items);

	$s_dir = $dir;
    if (strlen($s_dir) >50 )
        $s_dir = "..." . substr($s_dir,-47);
	show_header($GLOBALS["messages"]["actdir"].": "._breadcrumbs($dir));
	// show_header($GLOBALS["messages"]["actdir"].": /".get_rel_item("",$s_dir));

	// Javascript functions:
	include "./_include/javascript.php";

	// Sorting of items
	$_img = "&nbsp;<IMG width=\"10\" height=\"10\" border=\"0\" align=\"ABSMIDDLE\" src=\"_img/";
	if($GLOBALS["srt"]=="yes") {
		$_srt = "no";	$_img .= "_arrowup.gif\" ALT=\"^\">";
	} else {
		$_srt = "yes";	$_img .= "_arrowdown.gif\" ALT=\"v\">";
	}

	// Toolbar
	echo "<BR><TABLE width=\"95%\"><TR><TD><TABLE><TR>\n";

	// PARENT DIR
	echo "<TD><A HREF=\"".make_link("list", path_up($dir), NULL)."\">";
	echo "<IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" src=\"".$GLOBALS["baricons"]["up"]."\" ";
	echo "ALT=\"".$GLOBALS["messages"]["uplink"]."\" TITLE=\"".$GLOBALS["messages"]["uplink"]."\"></A></TD>\n";
	// HOME DIR
	echo "<TD><A HREF=\"".make_link("list",NULL,NULL)."\">";
	echo "<IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" src=\"".$GLOBALS["baricons"]["home"]."\" ";
	echo "ALT=\"".$GLOBALS["messages"]["homelink"]."\" TITLE=\"".$GLOBALS["messages"]["homelink"]."\"></A></TD>\n";
	// RELOAD
	echo "<TD><A HREF=\"javascript:location.reload();\"><IMG border=\"0\" width=\"16\" height=\"16\" ";
	echo "align=\"ABSMIDDLE\" src=\"".$GLOBALS["baricons"]["reload"]."\" ALT=\"".$GLOBALS["messages"]["reloadlink"];
	echo "\" TITLE=\"".$GLOBALS["messages"]["reloadlink"]."\"></A></TD>\n";
	// SEARCH
	echo "<TD><A HREF=\"".make_link("search",$dir,NULL)."\">";
	echo "<IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" src=\"".$GLOBALS["baricons"]["search"]."\" ";
	echo "ALT=\"".$GLOBALS["messages"]["searchlink"]."\" TITLE=\"".$GLOBALS["messages"]["searchlink"];
	echo "\"></A></TD>\n";

	echo "<TD>::</TD>";

    // print the download button
	_print_link("download_selected", permissions_grant($dir, NULL, "read"), $dir, NULL);

	// print the edit buttons
	_print_edit_buttons($dir);

	// ADMIN & LOGOUT
	if (login_is_user_logged_in())
	{
		echo "<TD>::</TD>";
		// ADMIN
		_print_link("admin",
				permissions_grant(NULL, NULL, "admin")
				|| permissions_grant(NULL, NULL, "password"),
				$dir, NULL);
		// LOGOUT
		_print_link("logout", true, $dir, NULL);
	}

	echo "<TD>::</TD>";
	//Languages


	foreach($GLOBALS["langs"] as $langs) {

		echo "<TD><A HREF=\"".make_link("list",$dir,NULL,NULL,NULL,$langs[0])."\">";

		if (!file_exists($langs[1]))
		{
			echo "&nbsp;$langs[0] ";
		}
		else
		{
			echo "<IMG border=\"0\" width=\"16\" height=\"11\" ";
			echo "align=\"ABSMIDDLE\" src=\"".$langs[1]."\" ALT=\"".$langs[0];
			echo "\" TITLE=\"".$langs[2]."\"/></A></TD>\n";
		}

		//list($slang,$img,$ext,$type)	= $mime;
		/*if(@eregi($ext,$item)) {
			$mime_type	= $desc;
			$image		= $img;
			if($query=="img"){ return $image;}
			else if($query=="ext"){ return $type;}
			else return $mime_type;
		*/

		}


	//

	echo "</TR></TABLE></TD>\n";

	// Create File / Dir
	if (permissions_grant($dir, NULL, "create"))
	{
		echo "<TD align=\"right\"><TABLE><FORM action=\"".make_link("mkitem",$dir,NULL)."\" method=\"post\">\n<TR><TD>";
		echo "<IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" src=\"".$GLOBALS["baricons"]["add"]."\" />";
		echo "<SELECT name=\"mktype\">";
		echo "<option value=\"file\">".$GLOBALS["mimes"]["file"]."</option>";
		echo "<option value=\"dir\">".$GLOBALS["mimes"]["dir"]."</option></SELECT>\n";
		echo "<INPUT name=\"mkname\" type=\"text\" size=\"15\">";
		echo "<INPUT type=\"submit\" value=\"".$GLOBALS["messages"]["btncreate"];
		echo "\"></TD></TR></FORM></TABLE></TD>\n";
	}

	echo "</TR></TABLE>\n";

	// End Toolbar


	// Begin Table + Form for checkboxes
	echo"<TABLE WIDTH=\"95%\"><FORM name=\"selform\" method=\"POST\" action=\"".make_link("post",$dir,NULL)."\">\n";
	echo "<INPUT type=\"hidden\" name=\"do_action\"><INPUT type=\"hidden\" name=\"first\" value=\"y\">\n";

	// Table Header
	echo "<TR><TD colspan=\"7\"><HR></TD></TR><TR><TD WIDTH=\"2%\" class=\"header\">\n";
	echo "<INPUT TYPE=\"checkbox\" name=\"toggleAllC\" onclick=\"javascript:ToggleAll(this);\"></TD>\n";
	echo "<TD WIDTH=\"44%\" class=\"header\"><B>\n";
	if($GLOBALS["order"]=="name") $new_srt = $_srt;	else $new_srt = "yes";
	echo "<A href=\"".make_link("list",$dir,NULL,"name",$new_srt)."\">".$GLOBALS["messages"]["nameheader"];
	if($GLOBALS["order"]=="name") echo $_img;
	echo "</A></B></TD>\n<TD WIDTH=\"10%\" class=\"header\"><B>";
	if($GLOBALS["order"]=="size") $new_srt = $_srt;	else $new_srt = "yes";
	echo "<A href=\"".make_link("list",$dir,NULL,"size",$new_srt)."\">".$GLOBALS["messages"]["sizeheader"];
	if($GLOBALS["order"]=="size") echo $_img;
	echo "</A></B></TD>\n<TD WIDTH=\"16%\" class=\"header\"><B>";
	if($GLOBALS["order"]=="type") $new_srt = $_srt;	else $new_srt = "yes";
	echo "<A href=\"".make_link("list",$dir,NULL,"type",$new_srt)."\">".$GLOBALS["messages"]["typeheader"];
	if($GLOBALS["order"]=="type") echo $_img;
	echo "</A></B></TD>\n<TD WIDTH=\"14%\" class=\"header\"><B>";
	if($GLOBALS["order"]=="mod") $new_srt = $_srt;	else $new_srt = "yes";
	echo "<A href=\"".make_link("list",$dir,NULL,"mod",$new_srt)."\">".$GLOBALS["messages"]["modifheader"];
	if($GLOBALS["order"]=="mod") echo $_img;
	echo "</A></B></TD><TD WIDTH=\"8%\" class=\"header\"><B>".$GLOBALS["messages"]["permheader"]."</B>\n";
	echo "</TD><TD WIDTH=\"6%\" class=\"header\"><B>".$GLOBALS["messages"]["actionheader"]."</B></TD></TR>\n";
	echo "<TR><TD colspan=\"7\"><HR></TD></TR>\n";

	// make & print Table using lists
	print_table($dir, make_list($dir_list, $file_list));

	// print number of items & total filesize
	echo "<TR><TD colspan=\"7\"><HR></TD></TR><TR>\n<TD class=\"header\"></TD>";
	echo "<TD class=\"header\">".$num_items." ".$GLOBALS["messages"]["miscitems"]." (";
    $free=parse_file_size(diskfreespace("/"));
	echo $GLOBALS["messages"]["miscfree"].": ".$free.")</TD>\n";
	echo "<TD class=\"header\">".parse_file_size($tot_file_size)."</TD>\n";

    echo "<TD class=\"header\" colspan=4></TD>";

	echo "</TR>\n<TR><TD colspan=\"7\"><HR></TD></TR></FORM></TABLE>\n";

?><script language="JavaScript1.2" type="text/javascript">
<!--
	// Uncheck all items (to avoid problems with new items)
	var ml = document.selform;
	var len = ml.elements.length;
	for(var i=0; i<len; ++i) {
		var e = ml.elements[i];
		if(e.name == "selitems[]" && e.checked == true) {
			e.checked=false;
		}
	}
// -->
</script><?php
}

// *** HELPER FUNCTIONS

function _print_edit_buttons ($dir)
{
	// for the copy button the user must have create and read rights
	_print_link("copy", permissions_grant_all($dir, NULL, array("create", "read")), $dir, NULL);
	_print_link("move", permissions_grant($dir, NULL, "change"), $dir, NULL);
	_print_link("delete", permissions_grant($dir, NULL, "delete"), $dir, NULL);
	_print_link("upload", permissions_grant($dir, NULL, "create") && get_cfg_var("file_uploads"), $dir, NULL);
	_print_link("archive",
		permissions_grant_all($dir, NULL, array("create", "read"))
			&& ($GLOBALS["zip"] || $GLOBALS["tar"] || $GLOBALS["tgz"]),
		$dir, NULL);
}

/**
  print out an button link in the toolbar.

  if $allow is set, make this button active and work, otherwise print
  an inactive button.
*/
function _print_link ($function, $allow, $dir, $item)
{
	// the list of all available button and the coresponding data
	$functions = array(
			"copy" => array("jfunction" => "javascript:Copy();",
					"image" => $GLOBALS["baricons"]["copy"],
					"imagedisabled" => $GLOBALS["baricons"]["notcopy"],
					"message" => $GLOBALS["messages"]["copylink"]),
			"move" => array("jfunction" => "javascript:Move();",
					"image" => $GLOBALS["baricons"]["move"],
					"imagedisabled" => $GLOBALS["baricons"]["notmove"],
					"message" => $GLOBALS["messages"]["movelink"]),
			"delete" => array("jfunction" => "javascript:Delete();",
					"image" => $GLOBALS["baricons"]["delete"],
					"imagedisabled" => $GLOBALS["baricons"]["notdelete"],
					"message" => $GLOBALS["messages"]["dellink"]),
			"upload" => array("jfunction" => make_link("upload", $dir, NULL),
					"image" => $GLOBALS["baricons"]["upload"],
					"imagedisabled" => $GLOBALS["baricons"]["notupload"],
					"message" => $GLOBALS["messages"]["uploadlink"]),
			"archive" => array("jfunction" => "javascript:Archive();",
					"image" => $GLOBALS["baricons"]["archive"],
					"message" => $GLOBALS["messages"]["comprlink"]),
			"admin" => array("jfunction" => make_link("admin", $dir, NULL),
					"image" => $GLOBALS["baricons"]["admin"],
					"message" => $GLOBALS["messages"]["adminlink"]),
			"logout" => array("jfunction" => make_link("logout", NULL, NULL),
					"image" => $GLOBALS["baricons"]["logout"],
					"imagedisabled" => "_img/_logout_.gif",
					"message" => $GLOBALS["messages"]["logoutlink"]),
			"edit" => array("jfunction" => make_link("edit", $dir, $item),
					"image" => $GLOBALS["baricons"]["edit"],
					"imagedisabled" => $GLOBALS["baricons"]["notedit"],
					"message" => $GLOBALS["messages"]["editlink"]),
			"unzip" => array("jfunction" => make_link("unzip", $dir, $item),
					"image" => $GLOBALS["baricons"]["unzip"],
					"imagedisabled" => $GLOBALS["baricons"]["notunzip"],
					"message" => $GLOBALS["messages"]["unziplink"]),
			"download" => array("jfunction" => make_link("download", $dir, $item),
					"image" => $GLOBALS["baricons"]["download"],
					"imagedisabled" => $GLOBALS["baricons"]["notdownload"],
					"message" => $GLOBALS["messages"]["downlink"]),
			"download_selected" => array("jfunction" => "javascript:DownloadSelected();",
					"image" => $GLOBALS["baricons"]["download"],
					"imagedisabled" => $GLOBALS["baricons"]["notdownload"],
					"message" => $GLOBALS["messages"]["download_selected"]),
			);

	// determine the functio nof this button and it's data
	$values = $functions[$function];

	// make an active link if the access is allowed
	if ($allow)
	{
		echo "<TD><A HREF=\"" . $values["jfunction"] . "\"><IMG border=\"0\" width=\"16\" height=\"16\" ";
		echo "align=\"ABSMIDDLE\" src=\"" . $values["image"] . "\" ALT=\"" . $values["message"];
		echo "\" TITLE=\"" . $values["message"] . "\"></A></TD>\n";
		return;
	}

	if (!isset($values["imagedisabled"]))
		return;

	// make an inactive link if the access is forbidden
	echo "<TD><IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" ";
	echo "src=\"" . $values["imagedisabled"] . "\" ALT=\"" . $values["message"] . "\" TITLE=\"";
	echo $values["message"] . "\"></TD>\n";

}

function _get_link_info($dir, $item)
{
    $type = get_mime_type($dir, $item, "type");

    if (! file_exists(get_abs_item($dir, $item)))
    {
        return '<span style="background:red;">'.$type.'</span>';
    }
    return $type;
}

/**
  The breadcrumbs function will take the user's current path and build a breadcrumb.

  A breadcrums is a list of links for each directory in the current path.

  @param    $curdir is a string containing what will usually be the users
            current directory.  %displayseparator is optional and contains a
            string that will be displayed betweenach crumb.

 Typical syntax:

     echo breadcrumbs($dir, ">>");
     show_header($GLOBALS["messages"]["actdir"].":".breadcrumbs($dir));
 */
function _breadcrumbs($curdir, $displayseparator = ' &raquo; ')
{
	//Get localized name for the Home directory
	$homedir = $GLOBALS["messages"]["homelink"];

    // Initialize first crumb and set it to the home directory.
	$breadcrumbs[] = "<a href=\"".make_link("list", "", NULL)."\">$homedir</a>";

	// Take the current directory and split the string into an array at each '/'.
	$patharray = explode('/', $curdir);

    // Find out the index for the last value in our path array
	 $lastx = array_keys($patharray);
	 $last = end($lastx);

    // Build the rest of the breadcrumbs
    $crumbdir = "";
    foreach ($patharray AS $x => $crumb)
    {
        // Add a new directory to the directory list so the link has the
        // correct path to the current crumb.
		$crumbdir = $crumbdir . $crumb;
		if ($x != $last):
            // If we are not on the last index, then create a link using $crumb
            // as the text.

			$breadcrumbs[] = "<a href=\"".make_link("list", $crumbdir, NULL)."\">$crumb</a>";

			// Add a separator between our crumbs.
			$crumbdir = $crumbdir . DIRECTORY_SEPARATOR;
		else:
			// Don't create a link for the final crumb.  Just display the crumb name.
            $breadcrumbs[] = $crumb;
		endif;
    }

	// Build temporary array into one string.
    return implode($displayseparator, $breadcrumbs);
}

?>
