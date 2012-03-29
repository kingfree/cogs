<?php
$br = (php_sapi_name() == "cli")? "":"<br>";

if(!extension_loaded('opencc')) {
	dl('opencc.' . PHP_SHLIB_SUFFIX);
	echo "dl\n";
}
$module = 'opencc';
$functions = get_extension_funcs($module);
print_r($functions);

$text = "你干什么不干我事。\n";

$od = opencc_open("zhs2zht.ini");
var_dump($od);
$text = opencc_convert($od, $text);

opencc_close($od);

echo $text;
