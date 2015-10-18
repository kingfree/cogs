<?php

require_once("_include/permissions.php");
require_once("_include/fun_extra.php");

// upload file
function upload_items($dir)
{
    _debug( "fun_up_uploadify.upload_items($dir)" );

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

    show_header($GLOBALS["messages"]["actupload"]);
?>

<link rel="stylesheet" type="text/css" href="_lib/uploadify/uploadify.css" />
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="_lib/uploadify/jquery.uploadify.min.js"></script>

<script type="text/javascript">
$(function() {
  $('#file_upload').uploadify({
    'debug'           : false,
    'swf'             : '_lib/uploadify/uploadify.swf',
    'uploader'        : '_lib/uploadify/uploader.php',
    'auto'            : false,
    'multi'           : true,
    'removeCompleted' : true,
    'buttonText'      : '<?php echo $GLOBALS['messages']['select_file'] ?>',
    'formData'        : { 'folder' : './<?php echo $dir;?>' },
    'onUploadSuccess' : function(file, data, response)
                        {
                            if (data != "1")
                                alert('Error: ' + data);
                        }
  });
});
</script>
<br><form enctype="multipart/form-data" action="<?php make_link("upload",$dir,NULL); ?>" method="post">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_max_file_size();?>">
        <input type="hidden" name="confirm" value="true">
        <input type="file" name="file_upload" id="file_upload" />
    <table>
            <tr>
                <td>
                    <input type="button"
                           onClick="javascript:$('#file_upload').uploadify('upload', '*')"
                           value="<?php echo $GLOBALS["messages"]["btnupload"];?>" >
                </td>
                <td>
                    <input type="button"
                           onClick="javascript:$('#file_upload').uploadify('cancel', '*')"
                           value="<?php echo $GLOBALS["messages"]["btnreset"];?>" >
                </td>
                <td>
                    <input type="submit" value="<?php echo $GLOBALS["error_msg"]["back"];?>">
                </td>
            </tr>
        </table>
        <p><?php echo($GLOBALS['messages']['note_upload_error']); ?></p>
    </form>
    <br>
<?php
	return;
}
?>
