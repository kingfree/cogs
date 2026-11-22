<?php
function langstrtonum($str) {
    switch ($str) {
        case 'pas':
            return 0;
        case 'c':
            return 1;
        case 'cpp':
            return 2;
        case 'zip':
            return 3;
    }
}

function langnumtostr($num) {
    switch ($num) {
        case 0:
            return 'pas';
        case 1:
            return 'c';
        case 2:
            return 'cpp';
        case 3:
            return 'zip';
    }
}

function 难度($K) {
    $V=floor($K / 2);
    $K%=2;
    $str="";
    for($i=1; $i<=$V; $i++)
        $str.="★";
    for($i=1; $i<=$K; $i++)
        $str.="☆";
    return $str;
}

function 评测结果($str, $mod=10, $end=false) {
    $res = "";
    for($i=0; $i<strlen($str); $i++) {
        if($str[$i] == 'A') $res .= "<span class='jA'>A</span>";
        else if($str[$i] == 'W') $res .= "<span class='jW'>W</span>";
        else if($str[$i] == 'T') $res .= "<span class='jT'>T</span>";
        else if($str[$i] == 'M') $res .= "<span class='jM'>M</span>";
        else if($str[$i] == 'E') $res .= "<span class='jE'>E</span>";
        else if($str[$i] == 'R') $res .= "<span class='jR'>R</span>";
        else if($str[$i] == 'C') $res .= "<span class='jC'>C</span>";
        else if($str[$i] == 'D') $res .= "<span class='jD'>D</span>";
        else if($str[$i] == 'N') $res .= "<span class='jN'>N</span>";
        else if($str[$i] == 'P') $res .= "<span class='jP'>P</span>";
        if(($i+1)>0 && ($i+1)%$mod==0) {
            $res.="<br />";
            if($end) {
    echo "<span class='judge'>".$res."</span>";
                return;
            }
        }
    }
    echo "<span class='judge'>".$res."</span>";
}

function 评测信息a($str) {
    $res = "";
    if($str == "") return "未评测";
    for($i=0; $i<strlen($str); $i++) {
        if($str[$i] == 'A') $res .= "<span class='jA'>答案正确</span>";
        else if($str[$i] == 'W') $res .= "<span class='jW'>答案错误</span>";
        else if($str[$i] == 'T') $res .= "<span class='jT'>超过时间限制</span>";
        else if($str[$i] == 'M') $res .= "<span class='jM'>超过内存限制</span>";
        else if($str[$i] == 'E') $res .= "<span class='jE'>运行时错误</span>";
        else if($str[$i] == 'R') $res .= "<span class='jR'>没有输出文件</span>";
        else if($str[$i] == 'C') $res .= "<span class='jC'>编译错误</span>";
        else if($str[$i] == 'D') $res .= "<span class='jD'>没有测试点数据</span>";
        else if($str[$i] == 'N') $res .= "<span class='jN'>没有源代码</span>";
        else if($str[$i] == 'P') $res .= "<span class='jP'>答案部分正确</span>";
        $res.="<br />";
    }
    return $res;
}

function 评测信息($str) {
    echo "<span class='judge'>".评测信息a($str)."</span>";
}

function 是否通过($pid, $q) {
    if ($_SESSION['ID']) {
        $sql = new SqlQuery("SELECT * FROM submit WHERE pid =? AND uid =? order by score desc limit 1", [$pid, $_SESSION['ID']]);
        $ac=$q->dosql($sql);
        if ($ac) {
            $e=$q->rtnrlt(0);
            //echo "<a href='".路径("problem/code.php?id=").$e['sid']."' target='_blank'><span class=".
            echo "<a href='".路径("submit/code.php?id=").$e['sid']."' target='_blank'><span class=icon-".
                ($e['accepted']?"ok>":"pause>")."</span></a>";
            //} else echo "<span class='did'>－</span>";
    } else echo "<span class='icon-minus'>－</span>";
}
}

function get_problem_diffarr() {
  $p = new DataAccess();
  $sql = new SqlQuery("select pid, difficulty from problem");
  $cnt = $p->dosql($sql);
  $probs = array();
  for ($i = 0; $i < $cnt; $i++) {
    $d = $p->rtnrlt($i);
    $pid = (int)$d['pid'];
    if (!$probs[$pid] || $probs[$pid] < $d['difficulty']) {
      $probs[$pid] = $d['difficulty'];
    }
  }
  return $probs;
}

function calc_grade($uid, $diffarr) {
  global $SET;
  $ww = 0.01;
  $pw = 2; //$SET['problem_weigth'];
  $cw = 1; //$SET['contest_weigth'];
  $pw *= $ww;
  $cw *= $ww;

  $uid = (int)($uid);
  if (!$uid) return;
  $p = new DataAccess();
  $sql = new SqlQuery("select uid, pid, score, accepted from submit where uid = ?", [$uid]);
  $cnt = $p->dosql($sql);
  $probs = array();
  for ($i = 0; $i < $cnt; $i++) {
    $d = $p->rtnrlt($i);
    $pid = (int)($d['pid']);
    if (!$probs[$pid] || $probs[$pid] < $d['score']) {
      $probs[$pid] = $d['score'];
    }
  }

  $submt = (int)($cnt);
  $accpd = 0;
  $grade = 0;

  foreach ($probs as $key => $value) {
    $pid = (int)($key);
    $score = (int)($value);
    if (!$pid) continue;
    if ($value == 100) $accpd++;
    //echo "$pid.$score!!";
    $diff = (int)($diffarr[$pid]) + 1;
    //echo "$grade->";
    $grade += $diff * $score * $pw;
    //echo "$grade. ";
  }

  $sql = new SqlQuery("select score, pid from compscore where uid = ?", [$uid]);
  $cnt = $p->dosql($sql);
  for ($i = 0; $i < $cnt; $i++) {
    $d = $p->rtnrlt($i);
    $pid = (int)($d['pid']);
    $score = (int)($d['score']);
    $diff = (int)($diffarr[$pid]) + 1;
    $grade += $diff * $score * $cw;
  }
  $grade = (int)$grade;
  //$grade = (int)($accpd * 2.3);

  $sql = new SqlQuery("update userinfo set submited = ?, accepted = ?, grade = ? where uid = ?", [$submt, $accpd, $grade, $uid]);
  $p->dosql($sql);

  return array($submt, $accpd, $grade);
}
