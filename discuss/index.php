<?php
require_once("../include/stdhead.php");
gethead(1,"","讨论版");
$p = new DataAccess();
$q = new DataAccess();

$pid = $_REQUEST['pid'];
$cid = $_REQUEST['cid'];
?>
<div id='discuss_bar'>
<a href="newpost.php?pid=<?=$pid?>">[发表新帖]</a>
当前位置：<a href="index.php">讨论版</a>
<? if($pid) { ?> &gt;&gt; <a href="index.php?pid=<?=$pid?>">题目 <?=$pid?></a> <? } ?>
</div>
<?
$sql = "SELECT `tid`, `title`, `top_level`, `topic`.`status`, `cid`, `pid`, CONVERT(MIN(`reply`.`time`),DATE) `posttime`, MAX(`reply`.`time`) `lastupdate`, `topic`.`author_id`, COUNT(`rid`) `count` FROM `topic`, `reply` WHERE `topic`.`status`!=2 AND `reply`.`status`!=2 AND `tid` = `topic_id`";
$sql.=" AND (`top_level` = 3 )";
if (array_key_exists("pid",$_REQUEST)&&$_REQUEST['pid']!=''){
  $sql.=" AND ( `pid` = '".mysql_escape_string($_REQUEST['pid'])."' OR `top_level` >= 2 )";
  $level="";
}
else
  $level=" - ( `top_level` = 1 AND `pid` != 0 )";
$sql.=" GROUP BY `topic_id` ORDER BY `top_level`$level DESC, MAX(`reply`.`time`) DESC";
$sql.=" LIMIT 30";

echo $sql;
$cnt = $p->dosql($sql);
?>
<table id='discuss'>
<tr>
<th></th>
<th>题目</th>
<th>作者</th>
<th>标题</th>
<th>发表时间</th>
<th>最后回复时间</th>
<th>回复</th>
</tr>
<?
if($cnt) {for ($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?><tr>
    <td>123</td>
</tr><?
} } else {
?><tr>
    <td colspan=7>没有帖子</td>
</tr><?
}
?>
</table>
<?
require_once("../include/stdtail.php");
?>
