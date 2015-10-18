<?php

require_once "./_include/user.php";
require_once "./_include/debug.php";

user_load();

session_start();

function login_check ()
{
    _debug("checking login");
    global $require_login;

    // if no login is required, there is nothing to do
    if (!$require_login)
    {
        _debug("no login required..");
        return;
    }

    // if the user is already authenticated, we're done
    _debug("login required, checking login");
    login();
}

//FIXME update home_dir variable if user is logged in
function login ()
{
    if ( isset( $_SESSION["s_user"] ) )
    {
        _debug("login(): session detected");
        if ( ! user_activate( $_SESSION["s_user"], $_SESSION["s_pass"] ))
        {
            _debug("Failed to activate user " . $_SESSION['s_user']);
            logout();
        }
    }
    else
    {
        if ( isset( $_POST["p_pass"] ) )
            $p_pass= $_POST["p_pass"];
        else
            $p_pass="";

        if ( isset( $_POST["p_user"] ) )
        {
            _debug("login(): login authentication");
            // Check Login
            if ( ! user_activate( stripslashes( $_POST["p_user"] ), md5( stripslashes( $p_pass ) ) ) )
            {
                global $error_msg;
                show_error( $error_msg["login_failed"] . ": " . $_POST["p_user"] );
            }
            // authentication sucessfull
            _debug( "user '" . $_POST[ "p_user" ]  . "' successfully authenticated" );

            // set language
            $_SESSION['language'] = qx_request("lang", "en");
            return;
        } else {
            // Ask for Login
            show_header($GLOBALS["messages"]["actlogin"]);
            echo "<BR><TABLE width=\"300\"><TR><TD colspan=\"2\" class=\"header\" nowrap><B>";
            echo $GLOBALS["messages"]["actloginheader"]."</B></TD></TR>\n<FORM name=\"login\" action=\"";
            echo make_link("login",NULL,NULL)."\" method=\"post\">\n";
            echo "<TR><TD>".$GLOBALS["messages"]["miscusername"].":</TD><TD align=\"right\">";
            echo "<INPUT name=\"p_user\" type=\"text\" size=\"25\"></TD></TR>\n";
            echo "<TR><TD>".$GLOBALS["messages"]["miscpassword"].":</TD><TD align=\"right\">";
            echo "<INPUT name=\"p_pass\" type=\"password\" size=\"25\"></TD></TR>\n";
            echo "<TR><TD>".$GLOBALS["messages"]["misclang"].":</TD><TD align=\"right\">";
            echo "<SELECT name=\"lang\">\n";
            @include "./_lang/_info.php";
            echo "</SELECT></TD></TR>\n";
            echo "<TR><TD colspan=\"2\" align=\"right\"><INPUT type=\"submit\" value=\"";
            echo $GLOBALS["messages"]["btnlogin"]."\"></TD></TR>\n</FORM></TABLE><BR>\n";
            ?><script language="JavaScript1.2" type="text/javascript">
                <!--
                if(document.login) document.login.p_user.focus();
            // -->
            </script><?php
                show_footer();
            exit;
        }
    }
}

function login_is_user_logged_in()
{
    return isset( $_SESSION["s_user"] );
}

function logout ()
{
    global $_SESSION;

    _debug("logging out user " . $_SESSION["s_user"]);
	$_SESSION = array();
	session_destroy();
	header("location: ".$GLOBALS["script_name"]);
}

?>
