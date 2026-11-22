<?php
function langstrtonum($str) { return Language::id($str); }
function langnumtostr($id) { return Language::TYPES[$id][0] ?? ''; }

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

function 是否通过($submission) {
    if (empty($_SESSION['ID'])) return;
    if (!$submission) { echo "<span class='icon-minus'>－</span>"; return; }
    $icon = $submission['accepted'] ? 'ok' : 'pause';
    echo '<a href="'.路径('submit/code.php?id='.(int)$submission['sid']).'"><span class="icon-'.$icon.'"></span></a>';
}
