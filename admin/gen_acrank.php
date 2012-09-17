<?php
require_once("../include/header.php");
gethead(1,"","生成等级");

$rand_gen_data_access=new DataAccess();

$subcnt=array();
$supcnt=array();
$accnt=array();

$sql="select * from problem";
$probcnt=$rand_gen_data_access->dosql($sql);

$sql="select submit.pid,uid,accepted,difficulty,score from submit,problem where problem.pid=submit.pid order by uid,pid,-score";
$cnt=$rand_gen_data_access->dosql($sql);
echo "计算题目等级积分……<br />";
$lastuser=0;
for ($i=0;$i<$cnt;$i++) {
    $d=$rand_gen_data_access->rtnrlt($i);
    $subcnt[ $d['uid'] ]++;
    if ($d['uid']>$lastuser) {
        $lastuser=$d['uid'];
        $accnt[ $lastuser ]=0;
        $grade[ $lastuser ]=0;
        $lastprob=0;
    }
    if ($d['pid']>$lastprob && $d['score']) {
        $lastprob=$d['pid'];
        if($d['accepted']) $accnt[ $lastuser ]++;
        $supcnt[ $lastuser ]++;
        $grade[ $lastuser ] += $d['difficulty'] * $d['score'] * $SET['problem_weight'] / 100.0;
    }
}
$sql="select compscore.score,compscore.uid,problem.difficulty,problem.pid from compscore,problem where problem.pid=compscore.pid order by uid,pid";
$cnt=$rand_gen_data_access->dosql($sql);
echo "计算比赛等级积分……<br />";
for ($i=0;$i<$cnt;$i++) {
    $d=$rand_gen_data_access->rtnrlt($i);
    $grade[$d['uid']] += $d['difficulty'] * $d['score'] * $SET['contest_weight'] / 100.0;
}

$users=$lastuser;
echo "更新数据库积分……<br />";
for ($i=1;$i<=$users;$i++) {
    $grade[$i]=(int) ($grade[$i] /* * ($accnt[$i] / $supcnt[$i])*/);
    $sql="update userinfo set accepted='{$accnt[$i]}',submited='{$subcnt[$i]}',grade='{$grade[$i]}' where uid='{$i}'";
    $rand_gen_data_access->dosql($sql);
}
echo "更新题目提交数据库……<br />";

$subcnt=array();
$accnt=array();
$sql="select pid,accepted from submit order by pid";
$cnt=$rand_gen_data_access->dosql($sql);
$lastprob=0;
for ($i=0;$i<$cnt;$i++) {
    $d=$rand_gen_data_access->rtnrlt($i);
    $subcnt[ $d['pid'] ]++;
    if ($d['accepted'])
        $accnt[ $d['pid'] ]++;
    if ($d['pid']>$lastprob)
        $lastprob=$d['pid'];
}
$probs=$lastprob;
for ($i=1;$i<=$probs;$i++) {
    $sql="update problem set acceptcnt='{$accnt[$i]}',submitcnt='{$subcnt[$i]}' where pid='{$i}'";
    $rand_gen_data_access->dosql($sql);
}
echo "<span class=ok>计算完成！</span>";

require_once("../include/footer.php");
?>
