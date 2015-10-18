<?php

require_once("_include/permissions.php");
require_once("_include/fun_extra.php");

// upload file
function upload_items($dir)
{
    _debug( "fun_up_ajaxupload.upload_items($dir)" );

    if (!permissions_grant($dir, NULL, "create"))
    {
        show_error($GLOBALS["error_msg"]["accessfunc"]);
    }

    if (isset($GLOBALS['__POST']["confirm"]) && $GLOBALS['__POST']["confirm"] == "true")
    {
        _debug( "linking to list($dir)" );
        header("Location: ".make_link("list",$dir,NULL));
        return;
    }

    global $no_access;
    $additional_header_information = file_get_contents("_lib/ajaxupload/upload.html");
    $additional_header_information = preg_replace("/@destination_folder@/", $dir, $additional_header_information);
    $additional_header_information = preg_replace("/@filter_pattern@/", "/$no_access/", $additional_header_information);
    show_header($GLOBALS["messages"]["actupload"], $additional_header_information);
?>

<br>
	<div id="example1" class="example">
		<div class="wrapper">
			<div id="button1" class="button">Upload</div>
		</div>
		<ol class="status"></ol>
		<p>Uploaded files:</p>
		<ol class="files"></ol>
	</div>
    <table>
            <tr>
                <td>
                    <input type="button" onClick="window.location = '<?php echo make_link("list", $dir, NULL); ?>';" value="<?php echo $GLOBALS["error_msg"]["back"];?>">
                </td>
            </tr>
        </table>
    </form>
    <br>
<?php
	return;
}
?>
