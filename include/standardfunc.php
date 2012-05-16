<?php

function encode($str) {
    return md5(sha1($str)."hasyzxcmykrgb123");
}

function rfile($fp) {
    $out="";
    if (is_resource($fp)) {
        while (!feof($fp))
            $out.= fgets($fp, 1024000);
    }
    return $out;
}

function deldir($dir) {
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);
    rmdir($dir);
}

function sp2n($s) {
    $o="";
    $len=strlen($s);
    for ($i=0; $i<$len; $i++) {
        if ($s[$i]==" ")
            $o.="&nbsp;";
        else if ($s[$i]=="    ")
            $o.="&nbsp;&nbsp;&nbsp;&nbsp;";
        else
            $o.=$s[$i];
    }
    return $o;
}

function echotablearray($r) {
    echo "<table border='1'>\n";
    foreach($r as $k=>$v) {
        if (is_array($v)) {
            if  (count($v)) {
                echo "<tr>\n<td>$k</td>\n<td>";
                echotablearray($v);
                echo "</td>\n</tr>\n";
            } else
                echo "<tr>\n<td>$k</td>\n<td>Null</td>\n</tr>\n";
        } else
            echo "<tr>\n<td>$k</td>\n<td>$v</td>\n</tr>\n";
    }
    echo "</table>\n";
}

function getfilesize($bytes) {
    if ($bytes >= pow(2,40)) {
        $return = round($bytes / pow(1024,4), 2);
        $suffix = "TB";
    }
    elseif ($bytes >= pow(2,30)) {
        $return = round($bytes / pow(1024,3), 2);
        $suffix = "GB";
    }
    elseif ($bytes >= pow(2,20)) {
        $return = round($bytes / pow(1024,2), 2);
        $suffix = "MB";
    }
    elseif ($bytes >= pow(2,10)) {
        $return = round($bytes / pow(1024,1), 2);
        $suffix = "KB";
    }
    else {
        $return = $bytes;
        $suffix = "B";
    }
    $return .= " " . $suffix;
    return $return;
}

function gettime() {
    list($usec, $sec) = explode(" ",microtime());
    return $sec.substr($usec,1);
}

function array_encode($arr) {
    $sa=array();
    $i=0;
    foreach($arr as $k=>$v) {
        $sa[$i]=base64_encode($k);
        $sa[$i+1]=base64_encode($v);
        $i+=2;
    }
    $s=implode("?",$sa);
    return base64_encode($s);
}

function array_decode($s) {
    $arr=array();
    $s=base64_decode($s);
    $sa=explode("?",$s);
    $i=0;
    $t="";
    foreach($sa as $k=>$v) {
        if ($i==0) {
            $t=base64_decode($v);
        } else {
            $arr[$t]=base64_decode($v);
        }
        $i=!$i;
    }
    return $arr;
}

function pathconvert($cur,$absp) { //当前文件，目标路径
    $cur = str_replace("\\","/",$cur);
    $absp = str_replace("\\","/",$absp);
    $sabsp=explode("/",$absp);
    $scur=explode("/",$cur);
    $la=count($sabsp)-1;
    $lb=count($scur)-1;
    $l=max($la,$lb);

    for ($i=0; $i<=$l; $i++) {
        if ($sabsp[$i]!=$scur[$i])
            break;
    }
    $k=$i-1;
    $path="";
    for ($i=1; $i<=($lb-$k-1); $i++)
        $path.="../";
    for ($i=$k+1; $i<=($la-1); $i++)
        $path.=$sabsp[$i]."/";
    $path.=$sabsp[$la];
    return $path;
}

function 路径($str) {
    global $SET;
    $str1 = $SET['base'].$str;
    return pathconvert($SET['cur'],$str1);
}

function 输出文本($S) {
    global $Query_Times,$SET,$cfg,$time_Ls;
    $S = str_replace("%global_sitename%",$SET['global_sitename'], $S);
    $S = str_replace("%style_profile%",$SET['style_profile'], $S);
    $S = str_replace("%global_adminname%",$SET['global_adminname'], $S);
    $S = str_replace("%global_adminaddress%",$SET['global_adminaddress'], $S);
    $S = str_replace("%constructiontime%",date("Y-m-d",$SET['global_constructiontime']), $S);
    $S = str_replace("%processtime%",round(gettime()-$time_Ls,4), $S);
    $S = str_replace("%querytimes%",$Query_Times, $S);
    return $S;
}

function langstrtonum($str) {
    switch ($str) {
    case 'pas':
        return 0;
    case 'c':
        return 1;
    case 'cpp':
        return 2;
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
    }
}

function getextend($file_name) {
    $extend = pathinfo($file_name);
    $extend = strtolower($extend["extension"]);
    return $extend;
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

function 评测结果($str) {
    $res = "";
    for($i=0; $i<strlen($str); $i++)
        if($str[$i] == 'A') $res .= "<span style='color:#0000FF;'>A</span>";
        else if($str[$i] == 'W') $res .= "<span style='color:#ff0000;'>W</span>";
        else if($str[$i] == 'T') $res .= "<span style='background-color:#0033FF;color:#FFFF00;'>T</span>";
        else if($str[$i] == 'M') $res .= "<span style='background-color:#00FF44;color:#000000;'>M</span>";
        else if($str[$i] == 'E') $res .= "<span style='background-color:#000000;color:#FFFF00;'>E</span>";
        else if($str[$i] == 'R') $res .= "<span style='background-color:#FFCC00;color:#006600;'>R</span>";
        else if($str[$i] == 'C') $res .= "<span style='background-color:#FF0000;'>C</span>";
        else if($str[$i] == 'D') $res .= "<span style='color:#fff;background-color:#000'>D</span>";
        else if($str[$i] == 'N') $res .= "<span style='color:#FFFFFF;'>N</span>";
        else if($str[$i] == 'P') $res .= "<span style='color:#B8860B;'>P</span>";
    echo "<span class='judge'>".$res."</span>";
}
function 评测信息($str) {
    $res = "";
    for($i=0; $i<strlen($str); $i++)
        if($str[$i] == 'A') $res .= "<span style='color:#0000FF;'>答案正确</span>";
        else if($str[$i] == 'W') $res .= "<span style='color:#ff0000;'>答案错误</span>";
        else if($str[$i] == 'T') $res .= "<span style='background-color:#0033FF;color:#FFFF00;'>超过时间限制</span>";
        else if($str[$i] == 'M') $res .= "<span style='background-color:#00FF44;color:#000000;'>超过内存限制</span>";
        else if($str[$i] == 'E') $res .= "<span style='background-color:#000000;color:#FFFF00;'>运行时错误</span>";
        else if($str[$i] == 'R') $res .= "<span style='background-color:#FFCC00;color:#006600;'>没有输出文件</span>";
        else if($str[$i] == 'C') $res .= "<span style='background-color:#FF0000;'>编译错误</span>";
        else if($str[$i] == 'D') $res .= "<span style='color:#fff;background-color:#000'>没有测试点数据</span>";
        else if($str[$i] == 'N') $res .= "<span style='color:#FFFFFF;'>没有源代码</span>";
        else if($str[$i] == 'P') $res .= "<span style='color:#B8860B;'>答案部分正确</span>";
    echo $res;
}
function 评测信息a($str) {
    $res = "";
    for($i=0; $i<strlen($str); $i++)
        if($str[$i] == 'A') $res .= "<span style='color:#0000FF;'>答案正确</span>";
        else if($str[$i] == 'W') $res .= "<span style='color:#ff0000;'>答案错误</span>";
        else if($str[$i] == 'T') $res .= "<span style='background-color:#0033FF;color:#FFFF00;'>超过时间限制</span>";
        else if($str[$i] == 'M') $res .= "<span style='background-color:#00FF44;color:#000000;'>超过内存限制</span>";
        else if($str[$i] == 'E') $res .= "<span style='background-color:#000000;color:#FFFF00;'>运行时错误</span>";
        else if($str[$i] == 'R') $res .= "<span style='background-color:#FFCC00;color:#006600;'>没有输出文件</span>";
        else if($str[$i] == 'C') $res .= "<span style='background-color:#FF0000;'>编译错误</span>";
        else if($str[$i] == 'D') $res .= "<span style='color:#fff;background-color:#000'>没有测试点数据</span>";
        else if($str[$i] == 'N') $res .= "<span style='color:#FFFFFF;'>没有源代码</span>";
        else if($str[$i] == 'P') $res .= "<span style='color:#B8860B;'>答案部分正确</span>";
    return $res;
}
function 是否通过($pid, $q) {
    if ($_SESSION['ID']) {
        $sql="SELECT * FROM submit WHERE pid ={$pid} AND uid ={$_SESSION['ID']} order by score desc limit 1";
        $ac=$q->dosql($sql);
        if ($ac) {
            $e=$q->rtnrlt(0);
            echo "<a href='".路径("problem/submitdetail.php?id=").$e['sid']."' target='_blank'><span class=".
                ($e['accepted']?"ok>√":"no>×")."</span></a>";
        } else echo "<span class='did'>－</span>";
    }
}

function 分页($total,$page,$url='',$page_size='',$max_length='') {
    if($total == 0) return;
    global $SET;
    //$total :总数
    //$page  :传递过来的当前页的值,第八页$page = 8;
    //$page_size :每页显示的数据的数目
    //$url   :传递的地址,默认为当前页面
    //$max_length:分页代码时候,中间的分页数的一半
    $page = ($page < 1) ? 1 : $page ;
    $page_size = $page_size ? $page_size : $SET['style_pagesize'];
    $url = $url ? $url : $_SERVER['PHP_SELF'] . '?';
    $max_length = $max_length ? $max_length : 5 ;
    $start = $page ? ($page - 1) * $page_size : 0;
    $total_page = ceil($total/$page_size);

    $page_table = '';
    //aways in the pages
    $page_table = '<div class="page_slice"><table align="center"><tr>';
    //显示第一页
    if($page == 1 )
        $page_table .= '<td class="current_page">1</td>';
    else
        $page_table .= '<td><a href="'.$url.'page=1">[首页]</a></td>';

    //循环中间页码
    if($total_page < $max_length*2) {
        $loop_start = 2;
        $loop_end = $total_page-1;
    } else {
        $loop_start = $page - $max_length;
        $loop_start = ($loop_start <2) ? 2 :$loop_start;
        $loop_end = $page + $max_length;
        $loop_end = ($loop_end < $max_length * 2) ? $max_length * 2:$loop_end;
        $loop_end = ($loop_end > $total_page) ? $total_page-1 :$loop_end;

    }

    //... link
    $link_start = (($loop_start - $max_length) < 2) ? 2 :$loop_start - $max_length;
    $link_end = (($loop_end + $max_length) > $total_page) ? $total_page :$loop_end + $max_length;

    if($loop_start > 2)
        $page_table .= '<td><a href="'.$url.'page='.$link_start.'">...</a></td>';

    //中间链接
    for($i = $loop_start ; $i <= $loop_end ; $i++) {
        if($page == $i)
            $page_table .= '<td class="current_page">'.$i.'</td>';
        else if($page == $i + 1)
            $page_table .= '<td><a href="'.$url.'page='.$i.'">[上一页]</a></td>';
        else if($page == $i - 1)
            $page_table .= '<td><a href="'.$url.'page='.$i.'">[下一页]</a></td>';
        else
            $page_table .= '<td><a href="'.$url.'page='.$i.'">['.$i.']</a></td>';
    }
    if($loop_end < $total_page-1)
        $page_table .= '<td><a href="'.$url.'page='.$link_end.'">...</a></td>';

    //末页链接
    if($total_page!=1) {
        if($page == $total_page)
            $page_table .= '<td class="current_page">'.$total_page.'</td>';
        else
            $page_table .= '<td><a href="'.$url.'page='.$total_page.'">[末页]</a></td>';
    }
    $page_table .= "</tr></table></div>";
    //输出分页代码
    echo $page_table;
}

function 异常($msg = "错误", $id = "") {
    global $SET;
    if($id == "") $id = base64_encode("/".$SET['global_root']);
    $id = base64_decode($id);
    echo '<div id="dialog"><div id="error_title">错误</div>';
    echo '<div id="dialog_text">'.$msg.'</div>';
    echo '<div id="dialog_button" onclick="url.go('.$id.');">确定</div></div>';
    echo '<meta http-equiv="refresh" content="'.$SET['style_jumptime'].'; URL='.$id.'" >';
    exit;
}

function 提示($msg = "提示", $id = "") {
    global $SET;
    if($id == "") $id = base64_encode("/".$SET['global_root']);
    else $id = base64_decode($id);
    echo $id;
    echo '<div id="dialog"><div id="dialog_title">提示</div>';
    echo '<div id="dialog_text">'.$msg.'</div>';
    echo '<div id="dialog_button" onclick="url.go('.$id.');">确定</div></div>';
    //echo '<meta http-equiv="refresh" content="'.$SET['style_jumptime'].'; URL='.$id.'" >';
    exit;
}

function 背景图片($uid=0) {
    global $SET;
    $portrait=$SET['base']."images/background";
    $path = pathconvert($SET['cur'],$portrait).'/';
    $backfile = $path . $uid . ".png";
    if(!file_exists($backfile) || filesize($backfile) < 1)
        $backfile = $path . "0.png";
    echo "<style type='text/css'>body {background-image: url($backfile);}</style>";
}
?>
