<?php
require_once("../include/header.php");

if (strlen($_POST['title'])>60) {
    异常("Your title is too long!");
    exit(0);
}
$p = new DataAccess();

$tid=null;
$cid=0;
if ($_REQUEST['action']=='new'){
    if ($_POST['title'] && $_POST['content']){
        if(array_key_exists('pid',$_REQUEST)&&$_REQUEST['pid']!='')
            $pid=$_REQUEST['pid'];
        else
            $pid=0;
        $sql="INSERT INTO `topic` (`title`, `author_id`, `cid`, `pid`) VALUES('".mysql_escape_string($_POST['title'])."', '".$_SESSION['ID']."', $cid, '".mysql_escape_string($pid)."')";
        $cnt = $p->dosql($sql);
            $sql = "select max(tid) as tid from topic";
            $cnt1 = $p->dosql($sql);
            $d=$p->rtnrlt(0);
            $tid = $d['tid'];
    }
    else
        异常('Error!');
}
if ($_REQUEST['action']=='reply' || !is_null($tid)) {
    if(is_null($tid)) $tid=$_POST['tid'];
    if (!is_null($tid) && array_key_exists('content', $_POST) && $_POST['content']!=''){
        $sql="INSERT INTO `reply` (`author_id`, `time`, `content`, `topic_id`,`ip`) VALUES('".$_SESSION['ID']."', NOW(), '".mysql_escape_string($_POST['content'])."', '".mysql_escape_string($tid)."','".$_SERVER['REMOTE_ADDR']."')";

        $cnt = $p->dosql($sql);
            header('Location: thread.php?tid='.$tid);
            exit(0);
    }
    else
        异常('Error!');
}
?>
