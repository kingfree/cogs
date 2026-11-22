<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
TestData::sendCase($_POST['filename'] ?? '', (int)($_POST['point'] ?? 0), $_POST['io'] ?? 'in');
