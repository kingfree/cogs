<?php

session_id($argv[1]);

parse_str($argv[2], $_GET);
parse_str($argv[2], $_POST);

require "index.php"
?>
