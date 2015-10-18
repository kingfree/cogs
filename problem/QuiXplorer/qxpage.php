<?php

/**
 * qxpage_selected_items
 * @return the selected items from a list view on a page out of the _POST.
 **/
function qxpage_selected_items()
{
  return $GLOBALS['__POST']["selitems"];
}
?>
