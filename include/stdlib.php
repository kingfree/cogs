<?php
header("Content-Type: text/html; charset=UTF-8");
session_start();
require_once("env.inc.php");
date_default_timezone_set('Asia/Shanghai');

global $Query_Times,$SET,$cfg,$time_Ls,$LIB;

class lib {
    public function cls_getsettings() {
        global $SET,$cfg;
        require_once("getsettings.php");
    }
    public function cls_reg() {
        require_once("registercheck.php");
    }
    public function cls_dbaccess() {
        require_once("db.php");
    }
    public function cls_database() {
        require_once("database.inc.php");
    }
    public function stdfunc() {
        require_once("standardfunc.php");
        require_once("gravatar.php");
    }
    public function cls_compile() {
        require_once("compobj.php");
    }
    public function cls_dbctrl() {
        require_once("dbcontrol.php");
    }
    public function cls_sendmail() {
        require_once("sendmail.inc.php");
    }
    public function cls_usersquare() {
        require_once("usersquare.inc.php");
    }
    public function func_socket() {
        require_once("socket.php");
    }
    public function anti_SQL_Injection() {
        require_once("asi.php");
    }
    public function get_userinfo($uid) {
        require_once("getuserinfo.php");
    }
    public function usersquare($uid) {
        require_once("usersquare.inc.php");
    }
    public function sendverfymail($uid) {
        global $SET;
        require_once("sendverfy.inc.php");
    }
    public function singlerank($p,$pid) {
        global $SET, $STR;
        require("singlerank.inc.php");
    }
    public function category_bar($p) {
        global $SET, $STR;
        require("categorybar.inc.php");
    }
    public function getsubgroup($p,$gid) {
        global $SET;
        require("getsubgroup.inc.php");
        return $g;
    }
    public function dpshhl() {
        global $SET;
        require("dpshhl.inc.php");
    }
    public function tradsimp() {
        global $SET;
        require("tradsimp.inc.php");
    }
}

$Query_Times=0;

$LIB=new lib;
$LIB->stdfunc();
if ($DBT==1)
    $LIB->cls_database();
else
    $LIB->cls_dbaccess();
$LIB->anti_SQL_Injection();
$LIB->cls_usersquare();


$time_Ls=gettime();

require_once("string.inc.php");
require_once("privilege.php");

?>
