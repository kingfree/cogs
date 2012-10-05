<?php
header("Content-Type: text/html; charset=UTF-8");
session_start();
require_once("env.inc.php");
date_default_timezone_set('Asia/Shanghai');

global $Query_Times,$SET,$cfg,$time_Ls,$LIB;

class lib {
    public function cls_getsettings() {
        global $SET,$cfg;
        require_once("sets.php");
    }
    public function cls_reg() {
        require_once("registercheck.php");
    }
    public function cls_dbaccess() {
        require_once("db.php");
    }
    public function stdfunc() {
        global $SET, $pri, $cfg;
        require_once("func.php");
        require_once("gravatar.php");
        require_once("privilege.inc.php");
    }
    public function cls_compile() {
        require_once("compobj.php");
    }
    public function func_socket() {
        require_once("socket.php");
    }
    public function get_userinfo($uid) {
        require_once("getuserinfo.php");
    }
    public function getsubgroup($p,$gid) {
        global $SET;
        require("getsubgroup.inc.php");
        return $g;
    }
    public function hlighter() {
        global $SET;
        require("hlighter.inc.php");
    }
    public function editor($edname) {
        global $SET;
        require("editor.inc.php");
    }
    public function mathjax($edname) {
        global $SET;
        require("mathjax.inc.php");
    }
    public function tradsimp() {
        global $SET;
        require("tradsimp.inc.php");
    }
    public function htmldom() {
        global $SET;
        require("simple_html_dom.php");
    }
}

$Query_Times=0;

$LIB=new lib;
$LIB->stdfunc();
$LIB->cls_dbaccess();
$time_Ls=gettime();

require_once("string.inc.php");

?>
