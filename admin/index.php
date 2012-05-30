<?php
require_once("../include/stdhead.php");
gethead(1,"可以管理","后台管理");
    $q=new DataAccess();
?>

<div class='row meng'>
<div class='span2 center'>
<h1>后台<br/>管理</h1>
<p class='well'>
<a href="rank/gen_acrank.php" class="btn"><i class='icon-retweet'></i>生成等级</a><br />
<a href="rank/clear_record.php" class="btn"><i class='icon-repeat'></i>清理垃圾</a>
</p>
</div>
<ul class='nav nav-list span2'>
<li class='nav-header'>题目</li>
<li class=''><a href="../problem/index.php"><i class='icon-list'></i>题目列表</a></li>
<li class=''><a href="problem/editprob.php?action=add"><i class='icon-glass'></i>添加新题目</a></li>
<li class='nav-header'>分类</li>
<li class=''><a href="../information/catelist.php"><i class='icon-tags'></i>分类列表</a></li>
<li class=''><a href="category/editcate.php?action=add"><i class='icon-tag'></i>添加新分类</a></li>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>比赛</li>
<li class=''><a href="../competition/index.php"><i class='icon-list-alt'></i>比赛列表</a></li>
<li class=''><a href="settings.php?settings=comp"><i class='icon-film'></i>比赛管理</a></li>
<li class=''><a href="comp/editcompbase.php?action=add"><i class='icon-fire'></i>添加新比赛</a></li>
<li class='nav-header'>评测</li>
<li class=''><a href="../information/grader.php"><i class='icon-calendar'></i>评测机列表</a></li>
<li class=''><a href="../admin/grader/editgrader.php?action=add"><i class='icon-share-alt'></i>添加评测机</a></li>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>用户</li>
<li class=''><a href="../problem/index.php"><i class='icon-user'></i>用户列表</a></li>
<li class=''><a href="user/loginlog.php"><i class='icon-bookmark'></i>登录记录</a></li>
<li class=''><a href="settings.php?settings=privilege"><i class='icon-book'></i>权限管理</a></li>
<li class='nav-header'>分组</li>
<li class=''><a href="../information/grouplist.php"><i class='icon-th'></i>分组列表</a></li>
<li class=''><a href="group/editgroup.php?action=add"><i class='icon-magnet'></i>添加新分组</a></li>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>评论</li>
<li class=''><a href="../information/comments.php"><i class='icon-comment'></i>评论列表</a></li>
<li class=''><a href="settings.php?settings=comments"><i class='icon-comment'></i>评论管理</a></li>
<li class='nav-header'>页面</li>
<li class=''><a href="../page/index.php"><i class='icon-file'></i>页面列表</a></li>
<li class=''><a href="../page/editpage.php?action=add"><i class='icon-edit'></i>添加新页面</a></li>
</ul>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>系统</li>
<li class=''><a href="settings.php?settings=settings"><i class='icon-cog'></i>参数设置</a></li>
<li class=''><a href="/phpmyadmin"><i class='icon-music'></i>phpMyAdmin</a></li>
<li class='nav-header'>管理</li>
<li class=''><a href="settings.php?settings=dbctrl"><i class='icon-minus'></i>数据库管理</a></li>
<li class=''><a href="settings.php?settings=terminal"><i class='icon-minus'></i>PHP终端访问</a></li>
<li class=''><a href="settings.php?settings=serverinfo"><i class='icon-minus'></i>服务器信息</a></li>
<li class=''><a href="settings.php?settings=phpinfo"><i class='icon-minus'></i>PHP信息</a></li>
</ul>
</div>
<?php
include_once("../include/stdtail.php");
?>
