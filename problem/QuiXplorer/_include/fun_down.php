<?php

require_once("_include/fun_archive.php");
require_once("_include/permissions.php");
require_once("qxpage.php");

/**
 * download_selected
 * @return void
 **/
function download_selected($dir)
{
    require_once("_include/fun_archive.php");
    $items = qxpage_selected_items();
    _download_items($dir, $items);
}

// download file
function download_item($dir, $item)
{
    _download_items($dir, array($item));
}

function _download_items($dir, $items)
{
    // check if user selected any items to download
    _debug("count items: '$items[0]'");
    if (count($items) == 0)
        show_error($GLOBALS["error_msg"]["miscselitems"]);

    // check if user has permissions to download
    // this file
    if ( ! _is_download_allowed($dir, $items) )
		show_error( $GLOBALS["error_msg"]["accessitem"] );

    // if we have exactly one file and this is a real
    // file we directly download it
    if ( count($items) == 1 && get_is_file( $dir, $items[0] ) )
    {
        $abs_item = get_abs_item($dir, $items[0]);
        _download($abs_item, $items[0]);
    }

    // otherwise we do the zip download
    zip_download( get_abs_dir($dir), $items );
}

function _download_header($filename, $filesize = 0) {
	$browser=id_browser();
	header('Content-Type: '.(($browser=='IE' || $browser=='OPERA')?
		'application/octetstream':'application/octet-stream'));
	header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Transfer-Encoding: binary');
    if ($filesize != 0)
    {
        header('Content-Length: '.$filesize);
    }
    header('Content-Disposition: attachment; filename="'.$filename.'"');
	if($browser=='IE') {
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
	} else {
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
	}
}

function _download($file, $localname)
{
    _download_header($localname, @filesize($file));
	@readfile($file);
	exit;
}

function _is_download_allowed( $dir, $items )
{
    foreach ($items as $file)
    {
        if (!permissions_grant($dir, $file, "read"))
            return false;

        if (!get_show_item($dir, $file))
            return false;

        if (!file_exists(get_abs_item( $dir, $file )))
            return false;
    }

    return true;
}
?>
