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

function __error($msg)
{
    _error($msg);
    echo $msg;
}

if (empty($_FILES))
{
    echo 'no files!';
    return 1;
}

// setup the target upload directory folder with full path name
// for security reasons in the _config/conf.php.
//

// check if the subfolder has been passed to this script
if (!isset($_POST['folder']))
{
    __error("unknown target folder in home directory!");
    return 1;
}

// determine the target folder
$targetFolder = realpath($GLOBALS['home_dir'] . DIRECTORY_SEPARATOR . $_POST['folder']);

// check if target folder directory exists. It dos not exist
// if the user has configured a different home directory than given
// in the global configuration.
if ( ! is_dir($targetFolder) )
{
    __error("target folder $targetfolder does not exist");
    return 1;
}

_debug("target folder is $targetFolder");

$tempFile = $_FILES['Filedata']['tmp_name'];
$targetFile = rtrim($targetFolder,'/') . "/" . $_FILES['Filedata']['name'];

// you may want to do some additional checks on the uploaded files
// here.
if (file_exists($targetFile))
{
    __error("target file $targetFile already exists!");
    return 1;
}

// We do not allow to upload files matching the
// global $no_access pattern. See _config/conf.php for details.
if (matches_noaccess_pattern($targetFile))
{
    __error("file $targetFile matches \$no_access pattern ($no_access)");
    return 1;
}

move_uploaded_file($tempFile, $targetFile);
echo '1';

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
