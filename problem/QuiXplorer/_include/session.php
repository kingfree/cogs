<?php
/**
	This function allows access to session variables
*/
function session_get ($name)
{
	$user = $_SESSION["s_user"];
	if ( ! isset ( $_SESSION ) )
		return;

	if ( ! isset( $_SESSION[$name] ) )
		return;
	
	return $_SESSION[$name];
}

/**
    Return true if the given file name matches the
    global $no_access pattern configured in _config/conf.php.
 */
function matches_noaccess_pattern($file)
{
    global $no_access;
    if ( !isset($no_access) || $no_access == "")
        return false;

    return preg_match( "%$no_access%", $file );
}


?>
