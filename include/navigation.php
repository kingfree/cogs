<?
function 链接($path, $text, $icon = "") {
    if($icon) $ttt = "<span class='icon-{$icon}'></span>";
    HTML("<a href=\"".路径($path)."\">{$ttt}{$text}</a>");
}
function 列表($path, $icon, $text) {
    HTML("<li>");
    链接($path, $text, $icon);
    HTML("</li>");
}
function Navigation($p) {
    global $SET;
    HTML("<div class='global_header'>");
    HTML("<ul class='list'>");
    HTML("<li class='logo'><a href='".路径("index.php")."' class='logo'>COGS</a></li>");
    if(有此权限('可以管理')) { 
        echo "<li class='admin'>";
        echo "<a href='".路径("admin/index.php")."'><span class='icon-asterisk'></span>后台</a>";
        echo "</li>\n";
    }
    列表("problem/index.php", "list", "题目");
    列表("information/catelist.php", "tags", "分类");
    列表("page/index.php", "file", "页面");
    列表("information/submitlist.php", "align-justify", "记录");
    列表("information/comments.php", "comment", "讨论");
    $context = "比赛";
    $now = time();
    $cnt2 = $p->dosql("select ctid from comptime where starttime < $now and endtime > $now");
    if($cnt2) $context .= "<span class='doing'>($cnt2)</span>";
    $cnt1 = $p->dosql("select ctid from comptime where starttime > $now and endtime > $now");
    if($cnt1) $context .= "<span class='todo'>($cnt1)</span>";
    列表("competition/index.php", "list-alt", $context);
    列表("user/index.php", "user", "用户");
    列表("information/grouplist.php", "th-large", "分组");
    列表("information/about.php", "info-sign", "关于");
    HTML("</ul>");
    HTML("<form class='search' method='get' action='".路径("problem/index.php")."'>");
    HTML("<input name='key' type='text' id='key' title='输入关键字按回车搜索题目，保持默认则为随机题目' value='随机题目' />");
    HTML("</form>");
    HTML("<ul class='person'>");
    if($uid = (int) $_SESSION['ID']) {
        $sql="select * from userinfo where uid='{$uid}'";
        $p->dosql($sql);
        $d=$p->rtnrlt(0);
        HTML("<li class='user'><a href='".路径("user/detail.php?uid={$uid}")."'><span class='username'>".$d['nickname']."</span><span class='avatar'>".gravatar::showImage($d['email'],28)."</span></a></li>");
        列表("user/panel.php", "cog", "设置");
        $mailtext = "信件";
        $cnt1 = $p->dosql("select mid from mail where readed = 0 and toid = {$uid}");
        if($cnt1 > 0) $mailtext .= "<span class='doing'>($cnt1)</span>";
        $cnt1 = $p->dosql("select mid from mail where readed = 0 and fromid = {$uid}");
        if($cnt1 > 0) $mailtext .= "<span class='todo'>($cnt1)</span>";
        列表("mail/index.php", "envelope", $mailtext);
        列表("user/dologout.php", "off", "退出");
    } else {
        HTML("<li class='user'><a href='".路径("user/login.php?from={$SET['URI']}")."'><span class='username'>登录</span><span class='avatar'>".gravatar::showImage("",28)."</span></a></li>");
        列表("user/register.php", "shopping-cart", "注册");
        列表("user/lost.php", "", "忘记密码");
    }
    HTML("</ul>");
    HTML("</div>");
}
?>
