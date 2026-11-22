<?php
require_once __DIR__.'/../include/header.php';
header('Content-Type: text/plain; charset=UTF-8');
$name = $_GET['name'] ?? '';
echo $name === '' ? 0 : (Account::byUsernameId($name)[0]['uid'] ?? 0);
