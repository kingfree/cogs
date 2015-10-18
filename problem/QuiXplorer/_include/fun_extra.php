<?php

require_once "_include/session.php";
require_once "_include/qxpath.php";
require_once "_include/str.php";

//------------------------------------------------------------------------------
// THESE ARE NUMEROUS HELPER FUNCTIONS FOR THE OTHER INCLUDE FILES
//------------------------------------------------------------------------------
function make_link($_action,$_dir,$_item=NULL,$_order=NULL,$_srt=NULL,$_lang=NULL) {
						// make link to next page
	if($_action=="" || $_action==NULL) $_action="list";
	if($_dir=="") $_dir=NULL;
	if($_item=="") $_item=NULL;
	if($_order==NULL) $_order=$GLOBALS["order"];
	if($_srt==NULL) $_srt=$GLOBALS["srt"];
	if($_lang==NULL) $_lang=(isset($GLOBALS["lang"])?$GLOBALS["lang"]:NULL);

	$link=$GLOBALS["script_name"]."?action=".$_action;
	if($_dir!=NULL) $link.="&dir=".urlencode($_dir);
	if($_item!=NULL) $link.="&item=".urlencode($_item);
	if($_order!=NULL) $link.="&order=".$_order;
	if($_srt!=NULL) $link.="&srt=".$_srt;
	if($_lang!=NULL) $link.="&lang=".$_lang;

	return $link;
}

function get_abs_dir($path)
{
    return path_f($path);
}

function get_abs_item($dir, $item) {		// get absolute file+path
	return get_abs_dir($dir).DIRECTORY_SEPARATOR.$item;
}
/**
  get file relative from home
 */
function get_rel_item($dir, $item)
{
    return $dir == "" ? $item : "$dir/$item";
}

/**
  can this file be edited?
  */
function get_is_file($dir, $item)
{
    $filename = get_abs_item($dir, $item);
	return @is_file($filename);
}

//------------------------------------------------------------------------------
function get_is_dir($dir, $item) {		// is this a directory?
	return @is_dir(get_abs_item($dir,$item));
}
//------------------------------------------------------------------------------
function parse_file_type($dir,$item) {		// parsed file type (d / l / -)
	$abs_item = get_abs_item($dir, $item);
	if(@is_dir($abs_item)) return "d";
	if(@is_link($abs_item)) return "l";
	return "-";
}
//------------------------------------------------------------------------------
function get_file_perms($dir,$item) {		// file permissions
	return @decoct(@fileperms(get_abs_item($dir,$item)) & 0777);
}
//------------------------------------------------------------------------------
function parse_file_perms($mode) {		// parsed file permisions
	if(strlen($mode)<3) return "---------";
	$parsed_mode="";
	for($i=0;$i<3;$i++) {
		// read
		if(($mode{$i} & 04)) $parsed_mode .= "r";
		else $parsed_mode .= "-";
		// write
		if(($mode{$i} & 02)) $parsed_mode .= "w";
		else $parsed_mode .= "-";
		// execute
		if(($mode{$i} & 01)) $parsed_mode .= "x";
		else $parsed_mode .= "-";
	}
	return $parsed_mode;
}

/**
  file size
  */
function get_file_size($dir, $item)
{
	return @filesize(get_abs_item($dir, $item));
}

function parse_file_size($size) {		// parsed file size
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . " GiB";
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . " MiB";
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . " KiB";
	} else $size = $size . " Bytes";
	if($size==0) $size="-";

	return $size;
}
//------------------------------------------------------------------------------
function get_file_date($dir, $item) {		// file date
	return @filemtime(get_abs_item($dir, $item));
}
//------------------------------------------------------------------------------
function parse_file_date($date) {		// parsed file date
	return @date($GLOBALS["date_fmt"],$date);
}
//------------------------------------------------------------------------------
function get_is_image($dir, $item) {		// is this file an image?
	if(!get_is_file($dir, $item)) return false;
	return @eregi($GLOBALS["images_ext"], $item);
}
//-----------------------------------------------------------------------------
function get_is_editable($dir, $item) {		// is this file editable?
	if(!get_is_file($dir, $item)) return false;
	foreach($GLOBALS["editable_ext"] as $pat) if(@eregi($pat,$item)) return true;
	return false;
}
//-----------------------------------------------------------------------------
function get_is_unzipable($dir, $item) {		// is this file editable?
	if(!get_is_file($dir, $item)) return false;
	foreach($GLOBALS["unzipable_ext"] as $pat) if(@eregi($pat,$item)) return true;
	return false;
}

function _get_used_mime_info ($item)
{
    foreach ($GLOBALS["used_mime_types"] as $mime)
    {
        list($desc, $img, $ext, $type) = $mime;
        if (@eregi($ext, $item))
            return array($mime_type, $image, $type);
    }

    return array(NULL, NULL, NULL);
}

/**
  determine the mime type of an item
 */
function get_mime_type ($dir, $item, $query)
{
    switch (filetype(get_abs_item($dir, $item)))
    {
        case "dir":
            $mime_type	= $GLOBALS["super_mimes"]["dir"][0];
            $image		= $GLOBALS["super_mimes"]["dir"][1];
            break;
        case "link":
            $mime_type	= $GLOBALS["super_mimes"]["link"][0];
            $image		= $GLOBALS["super_mimes"]["link"][1];
            break;
        default:
            list($mime_type, $image, $type) = _get_used_mime_info($item);
            if ($mime_type != NULL)
                break;

            if ((function_exists("is_executable") && @is_executable(get_abs_item($dir,$item)))
            || @eregi($GLOBALS["super_mimes"]["exe"][2], $item))
            {
                $mime_type	= $GLOBALS["super_mimes"]["exe"][0];
                $image		= $GLOBALS["super_mimes"]["exe"][1];
            }
            else
            {
                // unknown file
                $mime_type	= $GLOBALS["super_mimes"]["file"][0];
                $image		= $GLOBALS["super_mimes"]["file"][1];
            }
    }

    switch ($query)
    {
        case "img": return $image;
        case "ext": return $type;
        default:    return $mime_type;
    }
}

/**
    Check if user is allowed to access $file in $directory

 */
function get_show_item ($directory, $file)
{
    // no relative paths are allowed in directories
    if ( preg_match( "/\.\./", $directory ) )
        return false;

    if ( isset($file) )
    {
        // file name must not contain any path separators
        if ( preg_match( "/[\/\\\\]/", $file ) )
            return false;

        // dont display own and parent directory
        if ( $file == "." || $file == ".." )
            return false;

        // determine full path to the file
        $full_path = get_abs_item( $directory, $file );
        _debug("full_path: $full_path");
        if ( ! str_startswith( $full_path, path_f() ) )
            return false;
    }

    // check if user is allowed to acces shidden files
    global $show_hidden;
    if ( ! $show_hidden )
    {
        if ( $file[0] == '.' )
            return false;

        // no part of the path may be hidden
        $directory_parts = explode( "/", $directory );
        foreach ( $directory_parts as $directory_part )
        {
            if ( $directory_part[0] == '.' )
                return false;
        }
    }

    if (matches_noaccess_pattern($file))
        return false;

    return true;
}

//------------------------------------------------------------------------------
function copy_dir($source,$dest) {		// copy dir
	$ok = true;

	if ( !@mkdir($dest,0777) )
        return false;
	if ( ($handle = @opendir( $source ) ) === false)
        show_error($source."xx:".basename($source)."xx : ".$GLOBALS["error_msg"]["opendir"]);

	while(($file=readdir($handle))!==false) {
		if(($file==".." || $file==".")) continue;

		$new_source = $source."/".$file;
		$new_dest = $dest."/".$file;
		if(@is_dir($new_source)) {
			$ok=copy_dir($new_source,$new_dest);
		} else {
			$ok=@copy($new_source,$new_dest);
		}
	}
	closedir($handle);
	return $ok;
}

/**
    remove file / dir
 */
function remove ( $item )
{
	$ok = true;
	if(@is_link($item) || @is_file($item)) $ok=@unlink($item);
	elseif(@is_dir($item))
    {
		if(($handle=@opendir($item))===false)
            show_error($item.":".basename($item).": ".$GLOBALS["error_msg"]["opendir"]);

		while(($file=readdir($handle))!==false) {
			if(($file==".." || $file==".")) continue;

			$new_item = $item."/".$file;
			if(!@file_exists($new_item)) show_error(basename($item).": ".$GLOBALS["error_msg"]["readdir"]);
			//if(!get_show_item($item, $new_item)) continue;

			if(@is_dir($new_item)) {
				$ok=remove($new_item);
			} else {
				$ok=@unlink($new_item);
			}
		}

		closedir($handle);
		$ok=@rmdir($item);
	}
	return $ok;
}
//------------------------------------------------------------------------------
function get_max_file_size() {			// get php max_upload_file_size
	$max = get_cfg_var("upload_max_filesize");
	if(@eregi("G$",$max)) {
		$max = substr($max,0,-1);
		$max = round($max*1073741824);
	} elseif(@eregi("M$",$max)) {
		$max = substr($max,0,-1);
		$max = round($max*1048576);
	} elseif(@eregi("K$",$max)) {
		$max = substr($max,0,-1);
		$max = round($max*1024);
	}

	return $max;
}
//------------------------------------------------------------------------------
function down_home($abs_dir) {			// dir deeper than home?
	$real_home = @realpath($GLOBALS["home_dir"]);
	$real_dir = @realpath($abs_dir);

	if($real_home===false || $real_dir===false) {
		if(@eregi("\\.\\.",$abs_dir)) return false;
	} else if(strcmp($real_home,@substr($real_dir,0,strlen($real_home)))) {
		return false;
	}
	return true;
}
//------------------------------------------------------------------------------
function id_browser() {
	$browser=$GLOBALS['__SERVER']['HTTP_USER_AGENT'];

	if(preg_match('#Opera(/| )([0-9].[0-9]{1,2})#', $browser)) {
		return 'OPERA';
	} else if(preg_match('/MSIE ([0-9].[0-9]{1,2})/', $browser)) {
		return 'IE';
	} else if(preg_match('#OmniWeb/([0-9].[0-9]{1,2})#', $browser)) {
		return 'OMNIWEB';
	} else if(preg_match('#(Konqueror/)(.*)#', $browser)) {
		return 'KONQUEROR';
	} else if(preg_match('#Mozilla/([0-9].[0-9]{1,2})#', $browser)) {
		return 'MOZILLA';
	} else {
		return 'OTHER';
	}
}
//------------------------------------------------------------------------------
?>
