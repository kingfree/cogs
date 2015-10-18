<?php
/**
 footer for html-page
 */
function show_footer()
{
	?>
    <hr>
    <small>
        <a class="title" href="https://github.com/realtimeprojects/quixplorer" target="_blank"> QuiXplorer Version 2.5.7</a>
   </small>
   <small>Thanks for usage!</small>
   <?php show_login(); ?>
   </center>
   </body>
   </html>
   <?php
}

/**
  If no user is logged in, show the login option
  */
function show_login ()
{
	if (login_is_user_logged_in())
		return;
	echo '<small> - <a href="' . make_link("login", NULL) . '">' . $GLOBALS['messages']['btnlogin'] . "</a></small>";
}
?>
