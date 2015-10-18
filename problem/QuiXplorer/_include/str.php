<?php

function str_startswith ( $candidate, $search_str )
{
    return substr( $candidate, 0, strlen( $search_str) ) == $search_str;
}

?>
