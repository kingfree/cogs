<?php

$rand_gen_data_access=new DataAccess();

$subcnt=array();
$accnt=array();

$sql="select * from problem";
$probcnt=$rand_gen_data_access->dosql($sql);

$sql="select submit.pid,uid,accepted,difficulty,score from submit,problem where problem.pid=submit.pid order by uid,pid,-score";
$cnt=$rand_gen_data_access->dosql($sql);
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
        $grade[ $lastuser ] += $d['difficulty'] * $d['score'] / 10.0;
    }
}
$users=$lastuser;
for ($i=1;$i<=$users;$i++) {
    $grade[$i]=(int)$grade[$i];
    $sql="update userinfo set accepted='{$accnt[$i]}',submited='{$subcnt[$i]}',grade='{$grade[$i]}' where uid='{$i}'";
    $rand_gen_data_access->dosql($sql);
}

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

?>
