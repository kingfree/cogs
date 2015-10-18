<?php
/*
   Individual upload script for uploadify.

   This script is invoked when a successful upload has been made
   by uploadify to the temporary directory.

   This script allows to check the incoming and refures unwanted
   files.

   The task of this script is to move the incoming files to
   the correct target directory.

   Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
*/

require "../../_config/conf.php";
require "../../_include/session.php";
require "../../_include/debug.php";

function __status($msg)
{
    _error($msg);
    echo "$msg\n";
}

if (empty($_FILES))
{
    echo 'error: no files!';
    return 1;
}

// setup the target upload directory folder with full path name
// for security reasons in the _config/conf.php.
//

// check if the subfolder has been passed to this script
if (!isset($_POST['folder']))
{
    __status("error: unknown target folder in home directory!");
    return 1;
}

// determine the target folder
$targetFolder = realpath($GLOBALS['home_dir'] . DIRECTORY_SEPARATOR . $_POST['folder']);

// check if target folder directory exists. It dos not exist
// if the user has configured a different home directory than given
// in the global configuration.
if ( ! is_dir($targetFolder) )
{
    __status("error: target folder $targetfolder does not exist");
    return 1;
}

_debug("target folder is $targetFolder");
_debug(implode($_FILES['userfile']));
_debug(json_encode(array( 'files' => $_FILES, 'post' => $_POST)));
$userfile = $_FILES['userfile'];

for ($ii = 0; $ii < count($userfile['tmp_name']); $ii++)
{
    $tempFile = $userfile['tmp_name'][$ii];
    $targetFileName = $userfile['name'][$ii];
    $targetFile = rtrim($targetFolder,'/') . "/" . $targetFileName;

    // you may want to do some additional checks on the uploaded files
    // here.
    if (file_exists($targetFile))
    {
        __status("$targetFileName: already exists, skipping!");
        continue;
    }

    // We do not allow to upload files matching the
    // global $no_access pattern. See _config/conf.php for details.
    if (matches_noaccess_pattern($targetFile))
    {
        __status("$targetFileName: matches \$no_access pattern");
        continue;
    }

    _debug("moving $tempFile to $targetFile");
    move_uploaded_file($tempFile, $targetFile);
    __status("$targetFileName: Successfully uploaded");
}

/**
TODO:
    - currently, the implementation only works if the user has configured the same home
      directory like given in the global configuration as "home_dir", since we have
      no access to the session for authenticating the user.

Notes:
    -  We don't want to pass the absolute directory to the home directory
       by a post variable. This enables everybody to move a file
       from a random location on the server to any other
       location.
    - The session seams not to be valid within this script.
        -> We cannot determine the home directory of the user
           correctly
*/
?>
