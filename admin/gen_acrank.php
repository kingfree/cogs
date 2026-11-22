<?php
require_once("../include/header.php");
gethead(1,"","生成等级");

?>
<div class='page'>
开始计算……<br />
<?php
/*$subcnt=array();
$supcnt=array();
$accnt=array();

$rand_gen_data_access=new DataAccess();
$sql = new SqlQuery("select * from problem");
$probcnt=$rand_gen_data_access->dosql($sql);

    flush();
$sql = new SqlQuery("select submit.pid,uid,accepted,difficulty,score from submit,problem where problem.pid=submit.pid order by uid,pid,-score");
$cnt=$rand_gen_data_access->dosql($sql);
    flush();
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
    flush();
$sql = new SqlQuery("select compscore.score,compscore.uid,problem.difficulty,problem.pid from compscore,problem where problem.pid=compscore.pid order by uid,pid");
$cnt=$rand_gen_data_access->dosql($sql);
echo "计算比赛等级积分……<br />";
for ($i=0;$i<$cnt;$i++) {
    $d=$rand_gen_data_access->rtnrlt($i);
    $grade[$d['uid']] += $d['difficulty'] * $d['score'] * $SET['contest_weight'] / 100.0;
}
    flush();

$users=$lastuser;
echo "更新数据库积分……<br />";
for ($i=1;$i<=$users;$i++) {
    $grade[$i]=(int) ($grade[$i] /* * ($accnt[$i] / $supcnt[$i]));
    $sql = new SqlQuery("update userinfo set accepted=?,submited=?,grade=? where uid=?", [$accnt[$i], $subcnt[$i], $grade[$i], $i]);
    $rand_gen_data_access->dosql($sql);
}
    flush();
echo "更新题目提交数据库……<br />";

$subcnt=array();
$accnt=array();
$sql = new SqlQuery("select pid,accepted from submit order by pid");
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
    flush();
$probs=$lastprob;
for ($i=1;$i<=$probs;$i++) {
    $sql = new SqlQuery("update problem set acceptcnt=?,submitcnt=? where pid=?", [$accnt[$i], $subcnt[$i], $i]);
    $rand_gen_data_access->dosql($sql);
}
    flush();
*/
$diffarr = get_problem_diffarr();

$p = new DataAccess();
$sql = new SqlQuery("select uid, usr, realname from userinfo");
$cnt = $p->dosql($sql);
for ($i = 0; $i < $cnt; $i++) {
  $d = $p->rtnrlt($i);
  $uid = (int)($d['uid']);
  $username = ($d['realname']);
  $arr = calc_grade($uid, $diffarr);
  $submt = $arr[0];
  $accpd = $arr[1];
  $grade = $arr[2];
  if ($grade > 0) {
    echo "$username = <span class=ok>$grade</span> "; //<br />";
    echo "(<span class=no>$accpd</span>/";
    echo "<span class=did>$submt</span>). ";
  }
  flush();
}

?>

<span class=ok>计算完成！</span>
</div>
<?php
require_once("../include/footer.php");
?>
