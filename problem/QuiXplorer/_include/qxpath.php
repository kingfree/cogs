<?php

/**
  @returns the FullPath out of an RelativePath

  A FullPath is the full path including the home directory and a subdirectory
  below of it.

  If the home directory is set to '/var/www/data' in the conf.php ('home_dir'),
  and you provide a RelativePath of 'first_subdirectory', the function returns
  '/var/www/data/first_subdirectory'.

  This path is intended for internal use and not for presentation to the
  user, since he should only see relative pathes.
 
 */
function path_f ($path = '')
{
    global $home_dir;
    $abs_dir = $home_dir;
    switch ($path)
    {
        case '.':
        case '': return realpath($abs_dir);
    }
    
    return realpath(realpath($home_dir) . "/$path");
}

function path_r ($path)
{
    global $home_dir;
    $base = realpath($home_dir);
    $ret = preg_replace("#^$base#", "", $path);
    return $ret;
}

function path_up ($path)
{
    $ret = dirname($path);
    // make sure that we stop at the root directory
    // and convert the "." to an empty string
    return $ret == "." ?  "" : $ret;
}

?>
