<?php
/**
 * 列表从 检测页面(120, 2, 30) 得到数据库起始下标 30，再用 分页(120, 2, '?', 30) 绘制同一页。
 * 页码从 1 开始；空列表没有翻页栏。
 */

/** 把页码限制在实际范围，保证查询和当前高亮页一致。 */
function 检测页面($total, $page, $page_size = '')
{
    global $SET;
    $size = max(1, (int)($page_size ?: $SET['style_pagesize']));     // 旧站允许单个列表覆盖默认页长。
    $last = max(1, (int)ceil($total / $size));                      // 空列表也从第零条查起。
    return (max(1, min((int)$page, $last)) - 1) * $size;            // 将用户页码转为数据库的零起始下标。
}

/** 输出首尾、前后和页码窗口。url 是保留筛选参数的链接前缀。 */
function 分页($total, $page, $url = '', $page_size = '', $max_length = '')
{
    global $SET;
    if (!$total) return;                                            // 没有记录时无需翻页。
    $size = max(1, (int)($page_size ?: $SET['style_pagesize']));
    $last = (int)ceil($total / $size);                              // 尾页不足一整页仍可访问。
    $page = intdiv(检测页面($total, $page, $size), $size) + 1;        // 与实际查询的结果页严格一致。
    $width = max(1, (int)($max_length ?: 5));                       // 当前页的左右各显示多少个邻页。
    $url = h($url ?: $_SERVER['PHP_SELF'].'?');                      // 转义整段前缀，使原有筛选条件保留在链接中。

    $firstShown = max(2, $page - $width);                           // 首页、尾页单独显示，中间窗口不再重复。
    $lastShown = min($last - 1, max($width * 2, $page + $width));
    $links = [['首页', 1], ['上页', max(1, $page - 1)], [1, 1]];
    if ($firstShown > 2) $links[] = ['...', max(2, $firstShown - $width)]; // 省略号可再往前跳一个窗口。
    for ($number = $firstShown; $number <= $lastShown; $number++) $links[] = [$number, $number];
    if ($lastShown < $last - 1) $links[] = ['...', min($last, $lastShown + $width)];
    if ($last > 1) $links[] = [$last, $last];                       // 仅一页时只显示一次“1”。
    $links[] = ['下页', min($last, $page + 1)];                     // 首尾页的方向链接始终指向有效页。
    $links[] = ['尾页', $last];

    echo '<div class="pagination pagination-center"><ul>';
    foreach ($links as [$label, $target]) {                         // 数字标签可高亮，文字动作始终保留链接。
        echo $label === $page ? '<li class="active"><a>'.$page.'</a></li>'
            : '<li><a href="'.$url.'page='.$target.'">'.$label.'</a></li>';
    }
    echo '</ul></div>';
}
