<?php
require_once("../include/stdhead.php");
gethead(0,"","");

$p=new DataAccess();

$uid = $_REQUEST['username'];
$pwd = $_REQUEST['pwdhash'];
if ($uid == "") $uid = $_COOKIE['User'];
if ($pwd == "") $pwd = $_COOKIE['cojs_login'];
$sql="select * from userinfo where usr='".$uid."'";
$cnt=$p->dosql($sql);
if ($cnt==0)
    echo '<script>document.location="../error.php?id=2"</script>';
else
{    
    $d=$p->rtnrlt(0);
    if ($pwd==$d['pwdhash'] || (encode($_REQUEST['password'])==$d['pwdhash'] && ($_REQUEST['VerifyCode']==$_SESSION["IMGCODE"] || !$_SESSION["IMGCODE"]) ))
    {
        $LIB->get_userinfo($d['uid']);
        $q=new DataAccess();
        $sql="UPDATE `userinfo` SET `lastip` = '{$_SERVER['REMOTE_ADDR']}' WHERE `uid` ={$d['uid']}";
        $q->dosql($sql);
        if ($_REQUEST['savepwd'])
        {
            setcookie("cojs_login",$d['pwdhash'], time()+7776000);
            setcookie("User",$_POST['username'], time()+7776000);
        }
        if ($_REQUEST['from']=="")
            $_REQUEST['from']=base64_encode("/".$SETTINGS['global_root']);
        echo "<script>document.location=\"../refresh.php?id=2&go={$_REQUEST['from']}\"</script>";
    }
    else
    {
        echo '<script>document.location="../error.php?id=3"</script>';
    }
}
?>
