<?php

require_once "_include/header.php";

require_once "_include/debug.php";

/**
    show error-message and terminate
 */
function show_error($error,$extra=NULL)
{
    _error( $error . " : " . $extra );

    // we do not know whether the language module was already loaded
    $errmsg = isset($GLOBALS["error_msg"]) ? $GLOBALS["error_msg"]["error"] : "ERROR";
    $backmsg = isset($GLOBALS["error_msg"]) ? $GLOBALS["error_msg"]["back"] : "BACK";

	show_header($errmsg);
    ?>
	<center>
        <h2><?php echo $errmsg ?></h2>
        <?php echo $error ?>
        <h3> <a href="javascript:window.history.back()"><?php echo $backmsg ?></a><h3>
        <?php if ($extra != NULL) echo " - " . $extra; ?>
    </center>
    <?php
    show_footer();
    exit;
}
?>
