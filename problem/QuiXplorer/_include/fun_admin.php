<?php
/*------------------------------------------------------------------------------
     The contents of this file are subject to the Mozilla Public License
     Version 1.1 (the "License"); you may not use this file except in
     compliance with the License. You may obtain a copy of the License at
     http://www.mozilla.org/MPL/

     Software distributed under the License is distributed on an "AS IS"
     basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
     License for the specific language governing rights and limitations
     under the License.

     The Original Code is fun_admin.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.

     Alternatively, the contents of this file may be used under the terms
     of the GNU General Public License Version 2 or later (the "GPL"), in
     which case the provisions of the GPL are applicable instead of
     those above. If you wish to allow use of your version of this file only
     under the terms of the GPL and not to allow others to use
     your version of this file under the MPL, indicate your decision by
     deleting  the provisions above and replace  them with the notice and
     other provisions required by the GPL.  If you do not delete
     the provisions above, a recipient may use your version of this file
     under either the MPL or the GPL."
------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------
Author: The QuiX project
	quix@free.fr
	http://www.quix.tk
	http://quixplorer.sourceforge.net

Comment:
	Administrative Functions

	Have Fun...
------------------------------------------------------------------------------*/

require "./_include/permissions.php";

/**
 * Change Password & Manage Users Form
 */
function admin($admin, $dir)
{
	show_header($GLOBALS["messages"]["actadmin"]);

	// Javascript functions:
	include "./_include/js_admin.php";

	// Change Password
	echo "<BR><HR width=\"95%\"><TABLE width=\"350\"><TR><TD colspan=\"2\" class=\"header\"><B>";
	echo $GLOBALS["messages"]["actchpwd"].":</B></TD></TR>\n";
	echo "<FORM name=\"chpwd\" action=\"".make_link("admin",$dir,NULL)."\" method=\"post\">\n";
	echo "<INPUT type=\"hidden\" name=\"action2\" value=\"chpwd\">\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscoldpass"].": </TD><TD align=\"right\">";
	echo "<INPUT type=\"password\" name=\"oldpwd\" size=\"25\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscnewpass"].": </TD><TD align=\"right\">";
	echo "<INPUT type=\"password\" name=\"newpwd1\" size=\"25\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscconfnewpass"].": </TD><TD align=\"right\">";
	echo "<INPUT type=\"password\" name=\"newpwd2\" size=\"25\"></TD></TR>\n";
	echo "<TR><TD colspan=\"2\" align=\"right\"><INPUT type=\"submit\" value=\"".$GLOBALS["messages"]["btnchange"];
	echo "\" onClick=\"return check_pwd();\">\n</TD></TR></FORM></TABLE>\n";

	// Edit / Add / Remove User
	if($admin)
	{
		echo "<HR width=\"95%\"><TABLE width=\"350\"><TR><TD colspan=\"6\" class=\"header\" nowrap>";
		echo "<B>".$GLOBALS["messages"]["actusers"].":</B></TD></TR>\n";
		echo "<TR><TD colspan=\"5\">".$GLOBALS["messages"]["miscuseritems"]."</TD></TR>\n";
		echo "<FORM name=\"userform\" action=\"".make_link("admin",$dir,NULL)."\" method=\"post\">\n";
		echo "<INPUT type=\"hidden\" name=\"action2\" value=\"edituser\">\n";
		$cnt=count($GLOBALS["users"]);

		for($i = 0; $i < $cnt; ++$i)
		{
			// Username & Home dir:
			$user=$GLOBALS["users"][$i][0];	if(strlen($user)>15) $user=substr($user,0,12)."...";
			$home=$GLOBALS["users"][$i][2];	if(strlen($home)>30) $home=substr($home,0,27)."...";

			echo "<TR><TD width=\"1%\"><INPUT TYPE=\"radio\" name=\"user\" value=\"";
			echo $GLOBALS["users"][$i][0]."\"".(($i==0)?" checked":"")."></TD>\n";
			echo "<TD width=\"30%\">".$user."</TD><TD width=\"60%\">".$home."</TD>\n";
			echo "<TD width=\"3%\">".($GLOBALS["users"][$i][4]?$GLOBALS["messages"]["miscyesno"][2]:
				$GLOBALS["messages"]["miscyesno"][3])."</TD>\n";
			echo "<TD width=\"3%\">".$GLOBALS["users"][$i][6]."</TD>\n";
			echo "<TD width=\"3%\">".($GLOBALS["users"][$i][7]?$GLOBALS["messages"]["miscyesno"][2]:
				$GLOBALS["messages"]["miscyesno"][3])."</TD></TR>\n";
		}
		echo "<TR><TD colspan=\"6\" align=\"right\">";
		echo "<input type=\"button\" value=\"".$GLOBALS["messages"]["btnadd"];
		echo "\" onClick=\"javascript:location='".make_link("admin",$dir,NULL)."&action2=adduser';\">\n";
		echo "<input type=\"button\" value=\"".$GLOBALS["messages"]["btnedit"];
		echo "\" onClick=\"javascript:Edit();\">\n";
		echo "<input type=\"button\" value=\"".$GLOBALS["messages"]["btnremove"];
		echo "\" onClick=\"javascript:Delete();\">\n</TD></TR></FORM></TABLE>\n";
	}

	echo "<HR width=\"95%\"><input type=\"button\" value=\"".$GLOBALS["messages"]["btnclose"];
	echo "\" onClick=\"javascript:location='".make_link("list",$dir,NULL)."';\"><BR><BR>\n";
?><script language="JavaScript1.2" type="text/javascript">
<!--
	if(document.chpwd) document.chpwd.oldpwd.focus();
// -->
</script><?php
}


/**
 * Change Password
 */
function changepwd ($dir)
{
	$pwd=md5(stripslashes($GLOBALS['__POST']["oldpwd"]));
    $pw1 = $GLOBALS['__POST']["newpwd1"];
    $pw2 = $GLOBALS['__POST']["newpwd2"];
	if ($pw1 != $pw2)
    {
        show_error($GLOBALS["error_msg"]["miscnopassmatch"]);
    }

	$data = user_find($_SESSION["s_user"], $pwd);
    if($data == NULL)
        show_error($GLOBALS["error_msg"]["miscnouserpass"]);

	$data[1] = md5(stripslashes($pw1));
    if (!user_update($data[0], $data))
        show_error($data[0].": " . $GLOBALS["error_msg"]["chpass"]);
	user_activate($data[0], NULL);

	header("location: ".make_link("list", $dir, NULL));
}

/**
 * Add User
 */
function adduser ($dir)
{
	if(isset($GLOBALS['__POST']["confirm"]) && $GLOBALS['__POST']["confirm"]=="true")
	{
		$user=stripslashes($GLOBALS['__POST']["user"]);
		if($user=="" || $GLOBALS['__POST']["home_dir"]=="") {
			show_error($GLOBALS["error_msg"]["miscfieldmissed"]);
		}
		if($GLOBALS['__POST']["pass1"]!=$GLOBALS['__POST']["pass2"]) show_error($GLOBALS["error_msg"]["miscnopassmatch"]);
		$data=user_find($user,NULL);
        if ($data != NULL)
            show_error($user.": ".$GLOBALS["error_msg"]["miscuserexist"]);

		// determine the user permissions
		$permissions = _eval_permissions();

		$data=array($user,md5(stripslashes($GLOBALS['__POST']["pass1"])),
			stripslashes($GLOBALS['__POST']["home_dir"]),stripslashes($GLOBALS['__POST']["home_url"]),
			$GLOBALS['__POST']["show_hidden"],stripslashes($GLOBALS['__POST']["no_access"]),
			$permissions, $GLOBALS['__POST']["active"]);

		if(!user_add($data)) show_error($user.": ".$GLOBALS["error_msg"]["adduser"]);
		header("location: ".make_link("admin",$dir,NULL));
		return;
	}

	show_header($GLOBALS["messages"]["actadmin"].": ".$GLOBALS["messages"]["miscadduser"]);

	// Javascript functions:
	include "./_include/js_admin2.php";

	echo "<FORM name=\"adduser\" action=\"".make_link("admin",$dir,NULL)."&action2=adduser\" method=\"post\">\n";
	echo "<INPUT type=\"hidden\" name=\"confirm\" value=\"true\"><BR><TABLE width=\"450\">\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscusername"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"text\" name=\"user\" size=\"30\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscpassword"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"password\" name=\"pass1\" size=\"30\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscconfpass"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"password\" name=\"pass2\" size=\"30\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["mischomedir"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"text\" name=\"home_dir\" size=\"30\" value=\"";
		echo $GLOBALS["home_dir"]."\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["mischomeurl"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"text\" name=\"home_url\" size=\"30\" value=\"";
		echo $GLOBALS["home_url"]."\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscshowhidden"].":</TD>";
		echo "<TD align=\"right\"><SELECT name=\"show_hidden\">\n";
		echo "<OPTION value=\"0\">".$GLOBALS["messages"]["miscyesno"][1]."</OPTION>";
		echo "<OPTION value=\"1\">".$GLOBALS["messages"]["miscyesno"][0]."</OPTION>\n";
		echo "</SELECT></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["mischidepattern"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"text\" name=\"no_access\" size=\"30\" value=\"^\\.ht\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscperms"].":</TD>";

	// Permission settings
	echo "<TD align=\"right\">\n";
	admin_print_permissions(NULL);
	echo "</TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscactive"].":</TD>";
		echo "<TD align=\"right\"><SELECT name=\"active\">\n";
		echo "<OPTION value=\"1\">".$GLOBALS["messages"]["miscyesno"][0]."</OPTION>";
		echo "<OPTION value=\"0\">".$GLOBALS["messages"]["miscyesno"][1]."</OPTION>\n";
		echo "</SELECT></TD></TR>\n";
	echo "<TR><TD colspan=\"2\" align=\"right\"><input type=\"submit\" value=\"".$GLOBALS["messages"]["btnadd"];
		echo "\" onClick=\"return check_pwd();\">\n<input type=\"button\" value=\"";
		echo $GLOBALS["messages"]["btncancel"]."\" onClick=\"javascript:location='";
		echo make_link("admin",$dir,NULL)."';\"></TD></TR></FORM></TABLE><BR>\n";
?><script language="JavaScript1.2" type="text/javascript">
<!--
	if(document.adduser) document.adduser.user.focus();
// -->
</script><?php
}


/**
 * edit user
 */
function edituser($dir)
{
	// Determine the user name from the post data
	$user=stripslashes($GLOBALS['__POST']["user"]);

	// try to find the user
	$data=user_find($user,NULL);
	if($data==NULL) show_error($user.": ".$GLOBALS["error_msg"]["miscnofinduser"]);
	if($self=($user==$GLOBALS['__SESSION']["s_user"])) $dir="";

	if(isset($GLOBALS['__POST']["confirm"]) && $GLOBALS['__POST']["confirm"] == "true")
	{
		$nuser=stripslashes($GLOBALS['__POST']["nuser"]);
		if($nuser=="" || $GLOBALS['__POST']["home_dir"]=="")
		{
			show_error($GLOBALS["error_msg"]["miscfieldmissed"]);
		}

		if (isset($GLOBALS['__POST']["chpass"]) && $GLOBALS['__POST']["chpass"]=="true")
		{
            if ($GLOBALS['__POST']["pass1"]!=$GLOBALS['__POST']["pass2"])
                show_error($GLOBALS["error_msg"]["miscnopassmatch"]);
			$pass = md5(stripslashes($GLOBALS['__POST']["pass1"]));
		} else
        {
            $pass=$data[1];
        }

		if ($self)
            $GLOBALS['__POST']["active"] = 1;

		// determine the user permissions
		$permissions = _eval_permissions();

		// determine the new user data
		$data=array(
				$nuser,
				$pass,
				stripslashes($GLOBALS['__POST']["home_dir"]),
				stripslashes($GLOBALS['__POST']["home_url"]),
				$GLOBALS['__POST']["show_hidden"],
				stripslashes($GLOBALS['__POST']["no_access"]),
				$permissions,
				$GLOBALS['__POST']["active"]);

		if (!user_update($user,$data))
			show_error($user.": ".$GLOBALS["error_msg"]["saveuser"]);
		if ($self)
			user_activate($nuser, NULL);

		header("location: ".make_link("admin",$dir,NULL));
		return;
	}

	show_header($GLOBALS["messages"]["actadmin"].": ".sprintf($GLOBALS["messages"]["miscedituser"], $data[0]));

	// Javascript functions:
	include "./_include/js_admin3.php";

	echo "<FORM name=\"edituser\" action=\"".make_link("admin",$dir,NULL)."&action2=edituser\" method=\"post\">\n";
	echo "<INPUT type=\"hidden\" name=\"confirm\" value=\"true\"><INPUT type=\"hidden\" name=\"user\" value=\"".$data[0]."\">\n";
	echo "<BR><TABLE width=\"450\">\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscusername"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type\"text\" name=\"nuser\" size=\"30\" value=\"";
		echo $data[0]."\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscconfpass"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"password\" name=\"pass1\" size=\"30\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscconfnewpass"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"password\" name=\"pass2\" size=\"30\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscchpass"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"checkbox\" name=\"chpass\" value=\"true\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["mischomedir"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"text\" name=\"home_dir\" size=\"30\" value=\"";
		echo $data[2]."\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["mischomeurl"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"text\" name=\"home_url\" size=\"30\" value=\"";
		echo $data[3]."\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscshowhidden"].":</TD>";
		echo "<TD align=\"right\"><SELECT name=\"show_hidden\">\n";
		echo "<OPTION value=\"0\">".$GLOBALS["messages"]["miscyesno"][1]."</OPTION>";
		echo "<OPTION value=\"1\"".($data[4]?" selected ":"").">";
		echo $GLOBALS["messages"]["miscyesno"][0]."</OPTION>\n";
		echo "</SELECT></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["mischidepattern"].":</TD>\n";
		echo "<TD align=\"right\"><INPUT type=\"text\" name=\"no_access\" size=\"30\" value=\"";
		echo $data[5]."\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["miscperms"].":</TD>\n";

	// print out the extended permission table of the user permission
	echo "<TD align=\"right\">\n";
	admin_print_permissions($data[0]);
	echo "</TD></TR>\n";

	echo "<TR><TD>".$GLOBALS["messages"]["miscactive"].":</TD>";
		echo "<TD align=\"right\"><SELECT name=\"active\"".($self?" DISABLED ":"").">\n";
		echo "<OPTION value=\"1\">".$GLOBALS["messages"]["miscyesno"][0]."</OPTION>";
		echo "<OPTION value=\"0\"".($data[7]?"":" selected ").">";
		echo $GLOBALS["messages"]["miscyesno"][1]."</OPTION>\n";
		echo "</SELECT></TD></TR>\n";
	echo "<TR><TD colspan=\"2\" align=\"right\"><input type=\"submit\" value=\"".$GLOBALS["messages"]["btnsave"];
		echo "\" onClick=\"return check_pwd();\">\n<input type=\"button\" value=\"";
		echo $GLOBALS["messages"]["btncancel"]."\" onClick=\"javascript:location='";
		echo make_link("admin",$dir,NULL)."';\"></TD></TR></FORM></TABLE><BR>\n";
}

/**
 * remove user
 */
function removeuser($dir)
{
	$user=stripslashes($GLOBALS['__POST']["user"]);
	if($user==$GLOBALS['__SESSION']["s_user"]) show_error($GLOBALS["error_msg"]["miscselfremove"]);
	if(!user_remove($user)) show_error($user.": ".$GLOBALS["error_msg"]["deluser"]);

	header("location: ".make_link("admin",$dir,NULL));
}
//------------------------------------------------------------------------------
function show_admin($dir)
{
	$admin = permissions_grant(NULL, NULL, "admin");

	if (!login_is_user_logged_in())
		show_error($GLOBALS["error_msg"]["miscnofunc"]);
	if (!$admin && !permissions_grant(NULL, NULL, "password"))
		show_error($GLOBALS["error_msg"]["accessfunc"]);

	if(isset($GLOBALS['__GET']["action2"])) $action2 = $GLOBALS['__GET']["action2"];
	elseif(isset($GLOBALS['__POST']["action2"])) $action2 = $GLOBALS['__POST']["action2"];
	else $action2="";

	switch($action2) {
	case "chpwd":
		changepwd($dir);
	break;
	case "adduser":
		if(!$admin) show_error($GLOBALS["error_msg"]["accessfunc"]);
		adduser($dir);
	break;
	case "edituser":
		if(!$admin) show_error($GLOBALS["error_msg"]["accessfunc"]);
		edituser($dir);
	break;
	case "rmuser":
		if(!$admin) show_error($GLOBALS["error_msg"]["accessfunc"]);
		removeuser($dir);
	break;
	default:
		admin($admin,$dir);
	}
}
//------------------------------------------------------------------------------
/**
	print out the html permission table to modify user permissions.

	the name of the permission values are determined via the language
	interface. In case of there is no entry in the language table for
	this permission, the function uses the original permission name.
*/
function admin_print_permissions ($username)
{
	$permvalues = permissions_get();
	echo "<TABLE>";
	foreach ($permvalues as $name => $value)
	{
		// determine wether the option is already set
		$checked = permissions_grant_user($username, NULL, NULL, $name) ? "checked" : "";
		$disabled = (($username == "admin") && ($name == "admin")) ? "disabled" : "";
		$desc = $GLOBALS["messages"]["miscpermissions"][$name][0];
		$tooltip = $GLOBALS["messages"]["miscpermissions"][$name][1];
		echo "<TR><TD>\n";
		echo "\t\t<INPUT type=\"checkbox\" title=\"$tooltip\" name=\"permsettings[]\" value=\"$value\" $checked $disabled >\n";
		echo isset($desc) ? $desc : $name;
		echo "</INPUT>";
		echo "</TR></TD>";
	}
	echo "</TABLE>";
}


/**
  this function evaluates the changed permissions out of the html input form and convert this permissions
  into the permission values for storing them into the user database.
*/
function	_eval_permissions ()
{
	$perms = $GLOBALS['__POST']["permsettings"];
	$permissions = 0;
	if (!isset($perms))
		return $permissions;

	foreach ($perms as $values)
	{
		$permissions += $values;
	}

	return $permissions;
}

?>
