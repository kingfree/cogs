<?
require_once("../include/stdhead.php");
$did= (int) $_POST['fid'];
$pid= (int) $_POST['pid'];
$uid= (int) $_SESSION[ID];
echo $did.$pid.$uid;
$p = new DataAccess();
$q = new DataAccess();
if($_POST['showcode']) {
    $sql="select * from submit where uid='{$uid}' and pid='{$pid}' order by subtime desc";
    $q->dosql($sql);
    $e=$q->rtnrlt(0);
    $code = (int) $e['sid'];
} else $code = 0;
$sql = "insert into discuss(fid, uid, pid, cid, time, title, text, code) values(".$did.", ".$uid.", ".$pid.", 0, ".time().", '".$_POST['title']."', '".str_replace("'", "\'", $_POST['text'])."', ".(int)$code.")";
$ok = $p->dosql($sql);
echo '<script>document.location="'.$_POST['url'].'?did='.$did.'"</script>';
?>
