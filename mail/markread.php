<?php
require_once("../include/header.php");
require_once(dirname(__FILE__)."/message.php");
mailMarkRead(new DataAccess(), $_GET['mid'], $_SESSION['ID']);
