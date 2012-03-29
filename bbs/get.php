<?
require_once("../include/stdhead.php");
gethead(1,"","转换论坛数据");
$p = new DataAccess();
$q = new DataAccess();
$sql1 = "select * from comments order by stime asc";
$cnt=$p->dosql($sql1);
$prob = array();
$did = 0;
for($i=0;$i<$cnt;$i++) {
    $d = $p->rtnrlt($i);
    if($d['showcode']) { 
        $sql="select * from submit where uid='{$d['uid']}' and pid='{$d['pid']}' order by abs({$d['stime']}-subtime) asc";
        $q->dosql($sql);
        $e=$q->rtnrlt(0);
        $code = $e['sid'];
    } else $code = 0;
    if(!$prob[$d['pid']]) {
        $sql="select probname from problem where pid={$d['pid']}";
        $q->dosql($sql);
        $e=$q->rtnrlt(0);
        $title = "讨论题目 ".$e['probname'];
    } else $title = "";
    $sql = "insert into discuss(fid, uid, pid, cid, time, title, text, code) values(".(int)$prob[$d['pid']].", ".$d['uid'].", ".$d['pid'].", 0, ".$d['stime'].", '".$title."', '".str_replace("'", "\'", $d['detail'])."', ".(int)$code.")";
    $did++;
    if(!$prob[$d['pid']]) $prob[$d['pid']] = $did;
    $ok = $q->dosql($sql);
    if($ok) echo $sql."<br />";
    else { echo "<span style='color:red;'>".$sql."</span><br />";}
}
require_once("./include/stdtail.php");
?>
