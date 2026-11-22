<?php
function gettime() {
    list($usec, $sec) = explode(" ",microtime());
    return $sec.substr($usec,1);
}
