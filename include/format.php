<?php
function HTML($htmltext) {
    echo $htmltext . "\n";
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

function 输出文本($S) {
    global $Query_Times,$SET,$cfg,$time_Ls;
    $S = str_replace("%global_sitename%",$SET['global_sitename'], $S);
    $S = str_replace("%problem_weight%",$SET['problem_weight'], $S);
    $S = str_replace("%contest_weight%",$SET['contest_weight'], $S);
    $S = str_replace("%style_profile%",$SET['style_profile'], $S);
    $S = str_replace("%global_adminname%",$SET['global_adminname'], $S);
    $S = str_replace("%global_adminaddress%",$SET['global_adminaddress'], $S);
    $S = str_replace("%constructiontime%",date("Y-m-d", (int)strip_tags($SET['global_constructiontime'])), $S);
    $S = str_replace("%processtime%",round(gettime()-$time_Ls,4), $S);
    $S = str_replace("%querytimes%",$Query_Times, $S);
    return $S;
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

function shortname($longname) {
    $res = preg_replace("/\[.*\]/", "", $longname);
    $res = trim($res);
    return $res;
}

function BBCode($string) {
    $search = array(
        '/@(\S{1,20})/',
        '/@\[(.*?)\](\S{1,20})/',
        '/@(\S{1,20})\[(.*?)\]/',
        '/\[b\](.*?)\[\/b\]/is',
        '/\[i\](.*?)\[\/i\]/is',
        '/\[u\](.*?)\[\/u\]/is',
        '/\[img\](.*?)\[\/img\]/is',
        '/\[img (.*?)\](.*?)\[\/img\]/is',
        '/\[url\](.*?)\[\/url\]/is',
        '/\[url\=(.*?)\](.*?)\[\/url\]/is',
        '/\[color\=(.*?)\](.*?)\[\/color\]/is',
        '/\[size\=(.*?)\](.*?)\[\/size\]/is',
        '/\[code\](.*?)\[\/code\]/is',
        '/\[code=([^p][a-z]{0,5})\](.*?)\[\/code\]/is',
        '/\[code=pas([a-z]{0,5})\](.*?)\[\/code\]/is',
        '/\[label\](.*?)\[\/label\]/is',
        '/\[label\=(.*?)\](.*?)\[\/label\]/is',
        '/\[quote\](.*?)\[\/quote\]/is',
        '/\(([a-z]{2,5})(\d{1,5})\)/is',
    );
    $replace = array(
        '<a href="../user/detail.php?user=\\1" target="_blank" title="\\1">@\\1</a>',
        '<a href="../user/detail.php?user=\\1" target="_blank" title="\\1">@\\2</a>',
        '<a href="../user/detail.php?user=\\2" target="_blank" title="\\2">@\\1</a>',
        '<strong>\\1</strong>',
        '<em>\\1</em>',
        '<u>\\1</u>',
        '<img src="\\1">',
        '<img src="\\2" \\1>',
        '<a href="\\1" target="blank" title="\\1">\\1</a>',
        '<a href="\\1" target="blank" title="\\1">\\2</a>',
        '<span style="color:\\1;">\\2</span>',
        '<span style="font-size:\\1px;">\\2</span>',
        '<code>\\1</code>',
        //'<pre class="prettyprint linenums lang-\\1">\\2</pre>',
        '<pre><code class="\\1">\\2</code></pre>',
        '<pre><code class="delphi">\\2</code></pre>',
        '<span class="label">\\1</span>',
        '<span class="label label-\\1">\\2</span>',
        '<blockquote>\\1</blockquote>',
        '<img src="../images/\\1/\\2.gif" title="(\\1\\2)">'
    );
    $string = htmlspecialchars($string);
    $string = preg_replace($search, $replace, $string);
    return nl2br($string);
}

    function gettexts($string, $long) {
        if($long+3 >= strlen($string))
            return $string;
        return substr($string, 0, $long) . "...";
    }
