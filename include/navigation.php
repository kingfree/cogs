<?
function 链接($path, $text, $icon = "") {
    //if($icon) $ttt = "<span class='icon-{$icon}'></span>";
    HTML("<a href=\"".路径($path)."\">{$ttt}{$text}</a>");
}
function 列表($path, $icon, $text) {
    HTML("<li>");
    链接($path, $text, $icon);
    HTML("</li>");
}
function Navigation($p) {
    global $SET;
    HTML("<div id='nav' class='navbar navbar-fixed-top'>");
    HTML("<div class='navbar-inner'>");
    HTML("<div class='container-fluid'>");
    HTML("<a href='".路径("index.php")."' class='brand'>COGS</a>");
    HTML("<a class='btn btn-navbar' data-toggle='collapse' data-target='.nav-collapse'><span class='icon-bar'></span><span class='icon-bar'></span><span class='icon-bar'></span></a>");
    HTML("<div class='nav-collapse'>");
    HTML("<ul class='nav'>");
    HTML("<li class='dropdown'>");
    HTML("<a href='#' class='dropdown-toggle' data-toggle='dropdown'><b class='caret'></b></a>");
    HTML("<ul class='dropdown-menu span11'><li>");
    //HTML("<ul class='nav' id='catebar'>");
    HTML("<table id='catebar'>");
    $sql="select * from category order by cname";
    $cnt=$p->dosql($sql);
    for ($i=$st;$i<$cnt;$i++) {
        $d=$p->rtnrlt($i);
        if($i%8==0) HTML("<tr>");
        //HTML("<li class='span2'><a href='".路径("problem/index.php?caid={$d['caid']}")."' title='".sp2n(htmlspecialchars($d['memo']))."'>{$d['cname']}</a></li>");
        HTML("<td><a href='".路径("problem/index.php?caid={$d['caid']}")."' title='".sp2n(htmlspecialchars($d['memo']))."'>{$d['cname']}</a></td>");
    }
    //HTML("</ul>");
    HTML("</table>");
    HTML("</li></ul>");
    HTML("</li>");
    if(strpos($SET['cur'],"comment")) $active['comment']='active';
    else if(strpos($SET['cur'],"contest")) $active['contest']='active';
    else if(strpos($SET['cur'],"problem")) $active['problem']='active';
    else if(strpos($SET['cur'],"submit")) $active['submit']='active';
    else if(strpos($SET['cur'],"page")) $active['page']='active';
    else if(strpos($SET['cur'],"user")) $active['user']='active';
    else if(strpos($SET['cur'],"admin")) $active['admin']='active';
    HTML("<li class='{$active['problem']}'><a href='".路径("problem/index.php")."'>题目</a></li>");
    HTML("<li class='{$active['submit']}'><a href='".路径("submit/index.php")."'>记录</a></li>");
    $context = "比赛";
    $now = time();
    $cnt2 = $p->dosql("select ctid from comptime where starttime < $now and endtime > $now");
    if($cnt2) $context .= "<span class='doing'>($cnt2)</span>";
    $cnt1 = $p->dosql("select ctid from comptime where starttime > $now and endtime > $now");
    if($cnt1) $context .= "<span class='todo'>($cnt1)</span>";
    HTML("<li class='{$active['contest']}'><a href='".路径("contest/index.php")."'>$context</a></li>");
    HTML("<li class='{$active['page']}'><a href='".路径("page/index.php")."'>页面</a></li>");
    HTML("<li class='{$active['user']}'><a href='".路径("user/index.php")."'>用户</a></li>");
    HTML("<li class='{$active['comment']}'><a href='".路径("problem/commentlist.php")."'>评论</a></li>");
    HTML("<form class='navbar-search pull-left' method='get' action='".路径("problem/index.php")."'>");
    HTML("<input name='key' type='text' id='key' class='search-query span2'  placeholder='搜索题目' />");
    HTML("</form>");
    HTML("</ul>");
    HTML("<ul class='nav pull-right'>");
    if(有此权限('可以管理')) {
        HTML("<li class='{$active['admin']}'><a href='".路径("admin/index.php")."'>管理</a></li>");
        HTML("<li class='divider-vertical'></li>");
    }
    HTML("<li class='dropdown'>");
    if($uid = (int) $_SESSION['ID']) {
        $sql="select * from userinfo where uid='{$uid}'";
        $p->dosql($sql);
        $d=$p->rtnrlt(0);
        $nickname = $d['nickname'];
        $cnt1 = $p->dosql("select mid from mail where readed = 0 and toid = {$uid}");
        if($cnt1 > 0) $mails .= "<span class='doing'>($cnt1)</span>";
        $cnt1 = $p->dosql("select mid from mail where readed = 0 and fromid = {$uid}");
        if($cnt1 > 0) $mails .= "<span class='todo'>($cnt1)</span>";
    } else $nickname = "来宾用户";
    HTML("<a href='#' class='dropdown-toggle' data-toggle='dropdown'>{$nickname}{$mails}<b class='caret'></b></a>");
    HTML("<ul class='dropdown-menu'>");
    if($uid = (int) $_SESSION['ID']) {
        HTML("<li><a href='".路径("user/detail.php?uid={$uid}")."'><span class='username'>".$d['nickname']."</span><span class='avatar'>".gravatar::showImage($d['email'],28)."</span></a></li>");
        列表("user/panel.php", "cog", "设置");
        列表("mail/index.php", "envelope", "信件".$mails);
        列表("user/dologout.php", "off", "退出");
    } else {
        ?>
            <li><form method="post" action="<?=路径("user/dologin.php")?>" class='form-inline center'>
            <input name="from" type="hidden" id="from" value="<?=$SET['URI']?>" />
            <input name="username" type="text" class='input-small' placeholder='用户名' /><br />
            <input name="password" type="password" class='input-small' placeholder='密码' /><br />
            <!--<label class="checkbox">
            <input name="savepwd" type="checkbox" value="1" />记住
            <label>-->
            <button class='btn btn-primary'>登录</button>
            </form></li>
            <li class='divider'></li>
            <?
            列表("user/register.php", "", "注册");
        列表("user/lost.php", "", "忘记密码");
    }
    HTML("</ul>");
    HTML("</li>");
    HTML("</ul>");
    HTML("</div>");
    HTML("</div>");
    HTML("</div>");
    HTML("</div>");
}
?>
