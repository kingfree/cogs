<?php

function _idx ($what)
{
	$idx = array (
		'username' => 0,
		'password' => 1,
		'permissions' => 6,
		'useractive' => 7
		);
	return $idx[$what];
}

/**
	loads the user database for authenticating the users

    @param $file    The name of the file containing the user database. Default is ./_config/.htusers.php
*/
function user_load ($file = NULL)
{
    if (!isset($file))
        $file = "./_config/.htusers.php";

    if (! is_readable($file))
        show_error("user database $file does not exist or is not readable.<p>See the installation manual for details");

	require $file;
}

function _saveUsers ()
{
	$cnt=count($GLOBALS["users"]);
	if($cnt>0) sort($GLOBALS["users"]);

	// Make PHP-File
	$content='<?php $GLOBALS["users"]=array(';
	for($i=0;$i<$cnt;++$i) {
		// if($GLOBALS["users"][6]&4==4) $GLOBALS["users"][6]=7;	// If admin, all permissions
		$content.="\r\n\tarray(\"".$GLOBALS["users"][$i][0].'","'.
			$GLOBALS["users"][$i][1].'","'.$GLOBALS["users"][$i][2].'","'.$GLOBALS["users"][$i][3].'",'.
			$GLOBALS["users"][$i][4].',"'.$GLOBALS["users"][$i][5].'",'.$GLOBALS["users"][$i][6].','.
			$GLOBALS["users"][$i][7].'),';
	}
	$content.="\r\n); ?>";

	// Write to File
	$fp = @fopen("./_config/.htusers.php", "w");
	if($fp===false) return false;	// Error
	fputs($fp,$content);
	fclose($fp);

	return true;
}

/**
  @returns the index of the user in the user configuration
  @return -1 if the user was not found.
*/
function user_get_index ($user)
{
	if (!isset($GLOBALS['users']))
		return -1;

	// determine the number of registered users
	$cnt = count($GLOBALS["users"]);

	// search for the user with the given user name
	// in the user table
	for ($ii = 0; $ii < $cnt; ++$ii)
	{
		// look for the next entry if the current user dont
		// match the one we're looking for
		if ($user != $GLOBALS["users"][$ii][_idx('username')])
			continue;

		// return the index of the user
		return $ii;
	}

	// return -1 if the user has not been found
	return -1;

}
//------------------------------------------------------------------------------
/**
	try to find the user with the username $user and the password $pass
	in the user table.

	if you provide NULL as password, no password and user active check
	is done. otherwise, this function returns the user, if $pass matches
	the user password and the user is active.

	if the user is inactive or the password mismatches, NULL is returned.
*/
function user_find ($user, $pass = NULL)
{
	$idx = user_get_index($user);
	if ($idx < 0)
		return;

	// if no password check should be done, return
	// the user
	if ($pass == NULL)
		return $GLOBALS["users"][$idx];

	// check if the password matches
    $userpw = $GLOBALS["users"][$idx][_idx('password')];
	if ($pass != $userpw)
        return;

	// check if the user is active
	if (!$GLOBALS["users"][$idx][_idx('useractive')])
		return;

	// return the user if all checks are passed
	return $GLOBALS["users"][$idx];
}

//------------------------------------------------------------------------------
/**
	activate the user with the given user name and password.

	this function tries to find the user with the given user name and
	password in the user database and tries to activate this user.

	if username and password matches to the content of the
	user database, the user is activated, it's home directory,
	home url and permissions are set in the global variable and the
	function returns true.

	if the user cannot be authenticated, the function returns false.

	@param	$user	user name of the user to be authenticated
	@param	$pass	password of the user to authenticate
*/
function user_activate($user, $pass)
{
	// try to find and authenticate the user.
	$data = user_find($user, $pass);

	// if the user could not be authenticated, return false.
	if (!isset($data))
		return false;

	// store the user data in the globals variable
	$_SESSION["s_user"]	= $data[0];
	$_SESSION["s_pass"]	= $data[1];
	$GLOBALS["home_dir"]	= $data[2];
	$GLOBALS["home_url"]	= $data[3];
	$GLOBALS["show_hidden"]	= $data[4];
	$GLOBALS["no_access"]	= $data[5];

	// return true on success.
	return true;
}
//------------------------------------------------------------------------------
/**
	updates the user data for the given user.
*/
function user_update($user,$new_data)
{
	$idx = user_get_index($user);
	if ($idx < 0)
		return;

	$data=$new_data;
	$GLOBALS["users"][$idx] = $new_data;
	return _saveUsers();
}
//------------------------------------------------------------------------------
/**
	adds a new user to the user database.
*/
function user_add($data)
{
	if (user_find($data[0],NULL))
		return false;

	$GLOBALS["users"][] = $data;
	return _saveUsers();
}
//------------------------------------------------------------------------------

/**
  this function removes the user with the given user name from the
  user database.
*/
function user_remove ($user)
{
	// Copy Valid Users
	$cnt = count($GLOBALS["users"]);
	for ($i=0; $i < $cnt; ++$i)
	{
		if ($GLOBALS["users"][$i][0] != $user)
			$save_users[] = $GLOBALS["users"][$i];
	}
	$GLOBALS["users"]=$save_users;
	return _saveUsers();
}
//------------------------------------------------------------------------------
/**
  this function returns the permission values of the user with the given
  user name.

  if the user is not found in the user database, this function returns
  NULL, otherwise, it returns the permissions of the user.
*/
function user_get_permissions ($username)
{
	// try to find the user in the user database
	$data = user_find($username, NULL);

	// return NULL if the user does not exists
	if (!isset($data))
		return;

	// return the user permissions
	return $data[_idx('permissions')];
}
?>
