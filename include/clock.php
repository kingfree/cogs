<?php
/** 页面耗时用带小数的秒数表示。bootstrap.php 保存起始值，在页脚用 gettime() 减去它。 */
function gettime() {
    list($usec, $sec) = explode(" ",microtime());
    return $sec.substr($usec,1);
}
