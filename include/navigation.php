<?php
/**
 * 页头菜单按当前账号显示题库、比赛、提交及管理入口。隐藏菜单只影响显示，目标页面还要检查权限。
 * 例：Navigation();  // header.php 为普通浏览器调用，旧 IE 分支使用 Navigation_IE()。
 */
/** 输出带站内地址的导航链接，图标由模板选择。 */
function 链接($path, $text, $icon = "") {
    $ttt = '';
    if($icon) $ttt = "<span class='icon-{$icon}'></span>";
    HTML("<a href=\"".路径($path)."\">{$ttt}{$text}</a>");
}
/** 把站内入口输出为下拉菜单的一项。 */
function 列表($path, $icon, $text) {
    HTML("<li class='span2'>");
    链接($path, $text, $icon);
    HTML("</li>");
}
/** 输出普通浏览器的主导航，并按当前会话展示账号相关入口。 */
function Navigation() {
    global $SET;
    $mails = $ttt = '';
    $active = array_fill_keys(['comment','contest','problem','submit','page','user','docs','admin'], '');
    $navbar="";
    if($_SESSION['style']) $navbar="navbar-inverse";
    HTML("<div id='nav' class='navbar $navbar'>");
    HTML("<div class='navbar-inner'>");
    HTML("<div class='container-fluid'>");
    HTML("<a href='".路径("index.php")."' class='brand'>COGS</a>");
    HTML("<a class='btn btn-navbar' data-toggle='collapse' data-target='.nav-collapse'><span class='icon-bar'></span><span class='icon-bar'></span><span class='icon-bar'></span></a>");
    HTML("<div class='nav-collapse'>");
    HTML("<ul class='nav'>");
    HTML("<li class='dropdown'>");
    HTML("<a id='fenleibar' href='#' class='dropdown-toggle' data-toggle='dropdown'><b class='caret'></b></a>");
    HTML("<ul class='dropdown-menu span11' id='catebar'>");
    // 顶部菜单和题面使用同一份分类原文，说明里的空格和引号要在悬停提示中原样显示。
    foreach (Category::all() as $category) {
        $url = 路径('problem/index.php?caid='.(int)$category['caid']);
        $title = h($category['memo']);
        $name = h($category['cname']);
        HTML("<li style='width: 100px'><a href='$url' title='$title'>$name</a></li>");
    }
    HTML("</ul>");
    HTML("</li>");
    if(strpos($SET['cur'],"comment")) $active['comment']='active';
    else if(strpos($SET['cur'],"contest")) $active['contest']='active';
    else if(strpos($SET['cur'],"problem")) $active['problem']='active';
    else if(strpos($SET['cur'],"submit")) $active['submit']='active';
    else if(strpos($SET['cur'],"page")) $active['page']='active';
    else if(strpos($SET['cur'],"user")) $active['user']='active';
    else if(strpos($SET['cur'],"docs")) $active['docs']='active';
    else if(strpos($SET['cur'],"admin")) $active['admin']='active';
    HTML("<li class='{$active['problem']}'><a href='".路径("problem/index.php")."'>题目</a></li>");
    HTML("<li class='{$active['submit']}'><a href='".路径("submit/index.php")."'>记录</a></li>");
    $context = "比赛";
    $now = time();
    $counts = ContestRound::counts($now);
    $cnt2 = $counts['active'];
    if($cnt2) $context .= "<span class='doing'>($cnt2)</span>";
    $cnt1 = $counts['upcoming'];
    if($cnt1) $context .= "<span class='todo'>($cnt1)</span>";
    HTML("<li class='{$active['contest']}'><a href='".路径("contest/index.php")."'>$context</a></li>");
    HTML("<li class='{$active['page']}'><a href='".路径("page/index.php")."'>页面</a></li>");
    HTML("<li class='{$active['user']}'><a href='".路径("user/index.php")."'>用户</a></li>");
    HTML("<li class='{$active['comment']}'><a href='".路径("problem/comments.php")."'>评论</a></li>");
    HTML("<form class='navbar-search' method='get' action=\"".路径("problem/index.php")."\" >");
    HTML("<input name='key' type='text' id='key' class='search-query span2' placeholder='搜索题目' />");
    HTML("</form>");
    HTML("</ul>");
    HTML("<ul class='nav pull-right'>");
    HTML("<li class='{$active['docs']}'><a href='".路径("docs/index.php")."'>帮助</a></li>");
    if(Privilege::allows('可以管理'))
        HTML("<li class='{$active['admin']}'><a href='".路径("admin/index.php")."'>管理</a></li>");
    HTML("<li class='divider-vertical'></li>");
    HTML("<li class='dropdown'>");
    if($uid = (int) $_SESSION['ID']) {
        $d = $_SESSION;
        $nickname = $d['nickname'];
        $mailCounts = Message::unreadCounts($uid);
        $cnt1 = $mailCounts['received'];
        if($cnt1 > 0) $mails .= "<span class='doing'>($cnt1)</span>";
        $cnt1 = $mailCounts['sent'];
        if($cnt1 > 0) $mails .= "<span class='todo'>($cnt1)</span>";
    } else $nickname = "登录";
    HTML("<a href='#' class='dropdown-toggle' data-toggle='dropdown'>{$nickname}{$mails}<b class='caret'></b></a>");
    HTML("<ul class='dropdown-menu'>");
    if($uid = (int) $_SESSION['ID']) {
        HTML("<li class='span2'><a id='userbar' href='".路径("user/detail.php?uid={$uid}")."'><span class='username'>".h($d['nickname'])."</span><span class='avatar'>".gravatar::showImage($d['email'], 28)."</span></a></li>");
        列表("user/panel.php", "cog", "设置");
        列表("mail/index.php", "envelope", "信件".$mails);
        列表("user/dologout.php", "off", "退出");
    } else {
?>
<li class='span3'><form method="post" action="<?=路径("user/dologin.php")?>" class='form-inline center'><?=formTokenField()?>
<input name="from" type="hidden" id="from" value="<?=$SET['URI']?>" />
<input name="username" type="text" class='input-small span2' placeholder='用户名' /><br />
<input name="password" type="password" class='input-small span2' placeholder='密码' /><br />
<label class="checkbox">
<input name="savepwd" type="checkbox" value="1" />保存 Cookies
</label><br />
<button class='btn btn-primary'>登录</button>
</form></li>
<li class='divider'></li>
<?php
列表("user/register.php", "user", "注册");
列表("user/lost.php", "eye-open", "忘记密码");
}
HTML("</ul>");
HTML("</li>");
HTML("</ul>");
HTML("</div>");
HTML("</div>");
HTML("</div>");
HTML("</div>");
}

/** 输出旧 IE 使用的简化导航。 */
function Navigation_IE() {
    global $SET;
    $mails = $ttt = '';
    $active = array_fill_keys(['comment','contest','problem','submit','page','user','docs','admin'], '');
    $navbar="";
    if($_SESSION['style']) $navbar="navbar-inverse";
    HTML("<div id='nav' class='navbar $navbar'>");
    HTML("<div class='navbar-inner'>");
    HTML("<div class='container-fluid'>");
    HTML("<a href='".路径("index.php")."' class='brand'>COGS</a>");
    HTML("<a class='btn btn-navbar' data-toggle='collapse' data-target='.nav-collapse'><span class='icon-bar'></span><span class='icon-bar'></span><span class='icon-bar'></span></a>");
    HTML("<div class='nav-collapse'>");
    HTML("<ul class='nav'>");
    if(strpos($SET['cur'],"comment")) $active['comment']='active';
    else if(strpos($SET['cur'],"contest")) $active['contest']='active';
    else if(strpos($SET['cur'],"problem")) $active['problem']='active';
    else if(strpos($SET['cur'],"submit")) $active['submit']='active';
    else if(strpos($SET['cur'],"page")) $active['page']='active';
    else if(strpos($SET['cur'],"user")) $active['user']='active';
    else if(strpos($SET['cur'],"docs")) $active['docs']='active';
    else if(strpos($SET['cur'],"admin")) $active['admin']='active';
    HTML("<li class='{$active['problem']}'><a href='".路径("problem/index.php")."'>题目</a></li>");
    HTML("<li class='{$active['submit']}'><a href='".路径("submit/index.php")."'>记录</a></li>");
    $context = "比赛";
    $now = time();
    $counts = ContestRound::counts($now);
    $cnt2 = $counts['active'];
    if($cnt2) $context .= "<span class='doing'>($cnt2)</span>";
    $cnt1 = $counts['upcoming'];
    if($cnt1) $context .= "<span class='todo'>($cnt1)</span>";
    HTML("<li class='{$active['contest']}'><a href='".路径("contest/index.php")."'>$context</a></li>");
    HTML("<li class='{$active['page']}'><a href='".路径("page/index.php")."'>页面</a></li>");
    HTML("<li class='{$active['user']}'><a href='".路径("user/index.php")."'>用户</a></li>");
    HTML("<li class='{$active['comment']}'><a href='".路径("problem/comments.php")."'>评论</a></li>");
    HTML("</ul>");
    HTML("<ul class='nav pull-right'>");
    HTML("<li class='{$active['docs']}'><a href='".路径("docs/index.php")."'>帮助</a></li>");
    if(Privilege::allows('可以管理'))
        HTML("<li class='{$active['admin']}'><a href='".路径("admin/index.php")."'>管理</a></li>");
    HTML("<li class='divider-vertical'></li>");
    HTML("<li class='dropdown'>");
    if($uid = (int) $_SESSION['ID']) {
        $d = $_SESSION;
        $nickname = $d['nickname'];
        $mailCounts = Message::unreadCounts($uid);
        $cnt1 = $mailCounts['received'];
        if($cnt1 > 0) $mails .= "<span class='doing'>($cnt1)</span>";
        $cnt1 = $mailCounts['sent'];
        if($cnt1 > 0) $mails .= "<span class='todo'>($cnt1)</span>";
    } else $nickname = "登录";
    if($uid = (int) $_SESSION['ID']) {
    HTML("<li class=''><a href='".路径("user/detail.php?uid={$uid}")."'>{$nickname}{$mails}</a></li>");
    //HTML("<li class=''><a href='".路径("user/panel.php")."'>设置</a></li>");
    //HTML("<li class=''><a href='".路径("mail/index.php")."'>信件{$mails}</a></li>");
    HTML("<li class=''><a href='".路径("user/dologout.php")."'>退出</a></li>");
    } else {
    HTML("<li class=''><a href='".路径("user/login.php")."'>登录</a></li>");
    HTML("<li class=''><a href='".路径("user/register.php")."'>注册</a></li>");
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
