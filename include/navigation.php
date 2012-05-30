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
    HTML("<div class='navbar navbar-fixed-top'>");
    HTML("<div class='navbar-inner'>");
    HTML("<div class='container'>");
    HTML("<a href='".路径("index.php")."' class='brand'>COGS</a>");
    HTML("<div class='nav-collapse'>");
    HTML("<ul class='nav'>");
    HTML("<li class='dropdown'>");
    HTML("<a href='#' class='dropdown-toggle' data-toggle='dropdown'><b class='caret'></b></a>");
    HTML("<ul class='dropdown-menu span10'><li>");
    HTML("<ul class='nav' id='catebar'>");
    $sql="select * from category order by cname";
    $cnt=$p->dosql($sql);
    for ($i=$st;$i<$cnt;$i++) {
        $d=$p->rtnrlt($i);
        HTML("<li><a href='".路径("problem/index.php?caid={$d['caid']}")."' title='".sp2n(htmlspecialchars($d['memo']))."'>{$d['cname']}</a></li>");
    }
    HTML("</ul>");
    HTML("</li></ul>");
    HTML("</li>");
    列表("problem/index.php", "list", "题目");
    列表("information/submitlist.php", "align-justify", "记录");
    $context = "比赛";
    $now = time();
    $cnt2 = $p->dosql("select ctid from comptime where starttime < $now and endtime > $now");
    if($cnt2) $context .= "<span class='doing'>($cnt2)</span>";
    $cnt1 = $p->dosql("select ctid from comptime where starttime > $now and endtime > $now");
    if($cnt1) $context .= "<span class='todo'>($cnt1)</span>";
    列表("competition/index.php", "list-alt", $context);
    列表("page/index.php", "file", "页面");
    列表("user/index.php", "user", "用户");
    列表("information/comments.php", "comment", "讨论");
    HTML("<form class='navbar-search pull-left' method='get' action='".路径("problem/index.php")."'>");
    HTML("<input name='key' type='text' id='key' class='search-query span2'  placeholder='搜索题目' />");
    HTML("</form>");
    HTML("</ul>");
    HTML("<ul class='nav pull-right'>");
    if(有此权限('可以管理')) {
        列表("admin/index.php", "asterisk", "后台");
        HTML("<li class='divider-vertical'></li>");
    }
    HTML("<li class='dropdown'>");
    if($uid = (int) $_SESSION['ID']) {
        $sql="select * from userinfo where uid='{$uid}'";
        $p->dosql($sql);
        $d=$p->rtnrlt(0);
        $nickname = $d['nickname'];
    } else $nickname = "来宾用户";
    HTML("<a href='#' class='dropdown-toggle' data-toggle='dropdown'>{$nickname}<b class='caret'></b></a>");
    HTML("<ul class='dropdown-menu'>");
    if($uid = (int) $_SESSION['ID']) {
        HTML("<li><a href='".路径("user/detail.php?uid={$uid}")."'><span class='username'>".$d['nickname']."</span><span class='avatar'>".gravatar::showImage($d['email'],28)."</span></a></li>");
        列表("user/panel.php", "cog", "设置");
        $mailtext = "信件";
        $cnt1 = $p->dosql("select mid from mail where readed = 0 and toid = {$uid}");
        if($cnt1 > 0) $mailtext .= "<span class='doing'>($cnt1)</span>";
        $cnt1 = $p->dosql("select mid from mail where readed = 0 and fromid = {$uid}");
        if($cnt1 > 0) $mailtext .= "<span class='todo'>($cnt1)</span>";
        列表("mail/index.php", "envelope", $mailtext);
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
