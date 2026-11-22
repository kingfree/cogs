<?php
function 检测页面($total,$page,$page_size='') {
    global $SET;
    $page = (int) $page;
    $page_size = max(1, (int)($page_size ?: $SET['style_pagesize']));
    $total_page = ceil($total/$page_size);
    if($page < 1) return 0;
    if($total_page < 1) return 0;
    if($page > $total_page) $page = $total_page;
    $start = ($page - 1) * $page_size;
    return $start;
}

function 分页($total,$page,$url='',$page_size='',$max_length='') {
    if($total == 0) return;
    global $SET;
    //$total :总数
    //$page  :传递过来的当前页的值,第八页$page = 8;
    //$page_size :每页显示的数据的数目
    //$url   :传递的地址,默认为当前页面
    //$max_length:分页代码时候,中间的分页数的一半
    $page = max(1, (int)$page);
    $page_size = max(1, (int)($page_size ?: $SET['style_pagesize']));
    $url = h($url ?: $_SERVER['PHP_SELF'].'?');
    $max_length = $max_length ? $max_length : 5 ;
    $start = $page ? ($page - 1) * $page_size : 0;
    $total_page = ceil($total/$page_size);
    if($page > $total_page) $page = $total_page;

    $page_table = '';
    //aways in the pages
    $page_table = '<div class="pagination pagination-center"><ul>';
    //$page_table = '<div class="pager"><ul>';
    $page_table .= '<li><a href="'.$url.'page='.(1).'">首页</a></li>';
    $page_table .= '<li><a href="'.$url.'page='.($page-1).'">上页</a></li>';
    //显示第一页
    if($page == 1 )
        $page_table .= '<li class="active"><a>1</a></li>';
    else
        $page_table .= '<li><a href="'.$url.'page=1">1</a></li>';

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
        $page_table .= '<li><a href="'.$url.'page='.$link_start.'">...</a></li>';

    //中间链接
    for($i = $loop_start ; $i <= $loop_end ; $i++) {
        if($page == $i)
            $page_table .= '<li class="active"><a>'.$i.'</a></li>';
        else
            $page_table .= '<li><a href="'.$url.'page='.$i.'">'.$i.'</a></li>';
    }
    if($loop_end < $total_page-1)
        $page_table .= '<li><a href="'.$url.'page='.$link_end.'">...</a></li>';

    //末页链接
    if($total_page!=1) {
        if($page == $total_page)
            $page_table .= '<li class="active"><a>'.$total_page.'</a></li>';
        else
            $page_table .= '<li><a href="'.$url.'page='.$total_page.'">'.$total_page.'</a></li>';
    }
    $page_table .= '<li><a href="'.$url.'page='.($page+1).'">下页</a></li>';
    $page_table .= '<li><a href="'.$url.'page='.($total_page).'">尾页</a></li>';
    $page_table .= "</ul></div>";
    //输出分页代码
    echo $page_table;
}
