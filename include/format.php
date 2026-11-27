<?php
/**
 * 旧版网页的文本显示约定：短标题去掉方括号里的标签，讨论使用 BBCode，站点公告可显示参数值。
 * 例：echo BBCode('[b]欢迎参赛[/b]');
 */
/** 输出一行已组装的 HTML；调用方负责转义嵌入的普通文字。 */
function HTML($htmltext) {
    echo $htmltext . "\n";
}

/** 将空格转成网页能保留的间隔。用它显示普通文字时应先转义。 */
function sp2n($text) {
    return str_replace(' ', '&nbsp;', $text);
}

/** 用站点设置和本次请求统计替换公告模板中的占位符。 */
function 输出文本($template) {
    global $Query_Times, $SET, $requestStarted;
    // 按旧模板的替换次序展开，站名、管理员和积分权重来自本次加载的设置。
    foreach (['global_sitename','problem_weight','contest_weight','style_profile','global_adminname','global_adminaddress'] as $name) {
        $template = str_replace('%'.$name.'%', $SET[$name], $template);  // 只有此处列出的公开设置可嵌入公告。
    }
    $date = date('Y-m-d', (int)strip_tags($SET['global_constructiontime']));  // 旧库中的建站时间按 Unix 秒数保存。
    $template = str_replace('%constructiontime%', $date, $template);            // 前台统一以年月日展示。
    $elapsed = round(microtime(true) - $requestStarted, 4);                    // 从本次请求启动计时，单位是秒。
    $template = str_replace('%processtime%', $elapsed, $template);
    return str_replace('%querytimes%', $Query_Times, $template);                // 使用渲染这一刻已执行的查询次数。
}

/** 输出页面背景 CSS，优先使用该用户上传的背景图。 */
function 背景图片($uid=0) {
    $filename = (int)$uid.'.png';
    $file = publicFilePath('images/background/'.$filename); // 检查磁盘上的个人背景，URL 始终使用站内公开地址。
    if (!is_file($file) || filesize($file) === 0) $filename = '0.png';  // 旧安装用 0.png 作为公共背景。
    $url = h(路径('images/background/'.$filename));  // 浏览器只拿到当前安装目录下的公开地址。
    echo "<style type='text/css'>body {background-image: url($url);}</style>";
}

/** 移除旧题名中的方括号标签并转义，返回可嵌入 HTML 的短标题。 */
function shortname($longname) {
    $res = preg_replace("/\[.*\]/", "", $longname);  // 沿用旧列表的显示规则，隐藏首个 [ 到最后一个 ] 之间的标记。
    $res = trim($res);
    return h($res);  // 题名作为普通文字放进标题和提示属性。
}

/** 先转义评论原文，再转换 BBCode，并让 richText 限定输出的元素和属性。 */
function BBCode($string) {
    // 每一种写法与对应标记放在同一行；按旧规则的次序处理嵌套标签。
    $rules = [
        '/\[b\](.*?)\[\/b\]/is' => '<strong>\\1</strong>',  // 加粗。
        '/\[i\](.*?)\[\/i\]/is' => '<em>\\1</em>',  // 斜体。
        '/\[u\](.*?)\[\/u\]/is' => '<u>\\1</u>',  // 下划线。
        '/\[img\](.*?)\[\/img\]/is' => '<img src="\\1">',  // 显示图片。
        '/\[img (.*?)\](.*?)\[\/img\]/is' => '<img src="\\2" \\1>',  // 保留由富文本规则允许的图片属性。
        '/\[url\](.*?)\[\/url\]/is' => '<a href="\\1" target="blank" title="\\1">\\1</a>',  // 以地址本身作为链接文字。
        '/\[url\=(.*?)\](.*?)\[\/url\]/is' => '<a href="\\1" target="blank" title="\\1">\\2</a>',  // 链接显示作者填写的文字。
        '/\[color\=(.*?)\](.*?)\[\/color\]/is' => '<span style="color:\\1;">\\2</span>',  // 文字颜色。
        '/\[size\=(.*?)\](.*?)\[\/size\]/is' => '<span style="font-size:\\1px;">\\2</span>',  // 字号沿用像素单位。
        '/\[code\](.*?)\[\/code\]/is' => '<code>\\1</code>',  // 行内代码。
        '/\[code=([^p][a-z]{0,5})\](.*?)\[\/code\]/is' => '<pre><code class="\\1">\\2</code></pre>',  // 按语言名为代码块指定高亮。
        '/\[code=pas([a-z]{0,5})\](.*?)\[\/code\]/is' => '<pre><code class="delphi">\\2</code></pre>',  // Pascal 在旧高亮库中使用 delphi。
        '/\[label\](.*?)\[\/label\]/is' => '<span class="label">\\1</span>',  // 默认标签。
        '/\[label\=(.*?)\](.*?)\[\/label\]/is' => '<span class="label label-\\1">\\2</span>',  // 带样式的标签。
        '/\[quote\](.*?)\[\/quote\]/is' => '<blockquote>\\1</blockquote>',  // 引用段落。
        '/\(([a-z]{2,5})(\d{1,5})\)/is' => '<img src="../images/\\1/\\2.gif" title="(\\1\\2)">',  // 用原站点的表情组和编号查找 GIF。
    ];
    $string = htmlspecialchars($string);  // 先把评论中的原始 HTML 转成文字。
    // 一次识别三种提及写法，避免生成的链接再被下一条提及规则包进另一层链接。
    $string = preg_replace_callback('~@(?:\[([^\s\[\]/]+)\]([^\s\[\]]{1,20})|([^\s\[\]]{1,20})\[([^\s\[\]/]{1,24})\]|([^\s\[\]]{1,24}))~u', function ($match) {
        if ($match[1] !== '') {                            // @[账号]昵称
            [$account, $label] = [$match[1], $match[2]];
        } elseif ($match[3] !== '') {                      // @昵称[账号]，不能吞掉紧随其后的 [/b] 等结束标记。
            [$account, $label] = [$match[4], $match[3]];
        } else {
            $account = $label = $match[5];                 // @账号 同时作为链接目标和显示文字。
        }
        $query = rawurlencode(htmlspecialchars_decode($account));  // URL 里的账号作为一个完整查询值编码。
        return '<a href="../user/detail.php?user='.$query.'" title="'.$account.'">@'.$label.'</a>';
    }, $string);
    $string = preg_replace(array_keys($rules), array_values($rules), $string);  // 仅展开受支持的标记。
    return richText(nl2br($string));  // 保留换行，并由显示层过滤标签与图片链接。
}

/** 旧版显示按字节截取长字符串，省略部分以三个点表示。 */
function gettexts($string, $long) {
    if ($long + 3 >= strlen($string)) return $string;  // 比上限多三个字节以内时，完整展示与加省略号等长或更短。
    return substr($string, 0, $long).'...';             // 测试输入及输出摘要以字节数限制页面长度。
}
