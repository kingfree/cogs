<?php
/**
 * 把数据库里的语言编号和逐点结果字母显示给用户。语言表来自 Language，分数的计算位于 Judging。
 * 例：评测结果('AAWT', 10);  // 输出四个测试点。
 */
/** 根据源码扩展名取得评测协议中的语言编号。 */
function langstrtonum($str) { return Language::id($str); }
/** 将数据库里的语言编号转回扩展名，未知编号返回空串。 */
function langnumtostr($id) { return Language::TYPES[$id][0] ?? ''; }

/** 将难度整数转换成星级文字，每两点为一颗实心星。 */
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

/** 直接打印逐点判定的字母和颜色，以 mod 个测试点换行。 */
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

/** 返回逐点判定的中文说明 HTML；空结果表示尚未评测。 */
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

/** 把逐点中文说明输出到页面的评测结果区域。 */
function 评测信息($str) {
    echo "<span class='judge'>".评测信息a($str)."</span>";
}

/** 根据当前用户已有的提交输出状态图标；访客没有个人状态。 */
function 是否通过($submission) {
    if (empty($_SESSION['ID'])) return;
    if (!$submission) { echo "<span class='icon-minus'>－</span>"; return; }
    $icon = $submission['accepted'] ? 'ok' : 'pause';
    echo '<a href="'.路径('submit/code.php?id='.(int)$submission['sid']).'"><span class="icon-'.$icon.'"></span></a>';
}
