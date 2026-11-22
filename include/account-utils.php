<?php
function encode($str) {
    return md5(sha1($str)."hasyzxcmykrgb123");
}

function getip($ip) {
    $web="http://www.youdao.com/smartresult-xml/search.s?jsFlag=true&type=ip&q=$ip";
    $web="http://www.youdao.com/smartresult-xml/search.s?type=ip&q=$ip";
    $xml=simplexml_load_file($web);
    //var_dump($xml);
    $loc=$xml->product->location;
    return $loc;
}
