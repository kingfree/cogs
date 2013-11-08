<?php
require_once("../include/header.php");
gethead(1,"可以管理","后台管理");
$q=new DataAccess();
?>

<div class='page'>
<div class='row-fluid'>
<div class='span2 center'>
<h1 class='well'>后台<br/>管理</h1>
</div>
<ul class='nav nav-list span2'>
<li class='nav-header'>题目</li>
<li class=''><a href="../problem/index.php"><i class='icon-list'></i>题目列表</a></li>
<li class=''><a href="../problem/editprob.php?action=add"><i class='icon-glass'></i>添加新题目</a></li>
<!--
<li class=''><a href="../problem/problem_import.php"><i class='icon-glass'></i>导入题目</a></li>
<li class=''><a href="../problem/problem_export.php"><i class='icon-glass'></i>导出题目</a></li>
-->
<li class=''><a href="../problem/testdata.php"><i class='icon-tasks'></i>查看数据</a></li>
<li class='nav-header'>分类</li>
<li class=''><a href="../problem/catelist.php"><i class='icon-tags'></i>分类列表</a></li>
<li class=''><a href="../problem/editcate.php?action=add"><i class='icon-tag'></i>添加新分类</a></li>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>比赛</li>
<li class=''><a href="../contest/index.php"><i class='icon-list-alt'></i>比赛列表</a></li>
<li class=''><a href="../contest/compbase.php"><i class='icon-film'></i>比赛管理</a></li>
<li class=''><a href="../contest/editcompbase.php?action=add"><i class='icon-fire'></i>添加新比赛</a></li>
<li class='nav-header'>评测</li>
<li class=''><a href="../submit/graderlist.php"><i class='icon-calendar'></i>评测机列表</a></li>
<li class=''><a href="../submit/editgrader.php?action=add"><i class='icon-share-alt'></i>添加评测机</a></li>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>用户</li>
<li class=''><a href="../user/index.php"><i class='icon-user'></i>用户列表</a></li>
<li class=''><a href="../user/loginlog.php"><i class='icon-bookmark'></i>登录记录</a></li>
<li class=''><a href="../user/privilege.php"><i class='icon-book'></i>权限管理</a></li>
<li class='nav-header'>分组</li>
<li class=''><a href="../user/grouplist.php"><i class='icon-th'></i>分组列表</a></li>
<li class=''><a href="../user/editgroup.php?action=add"><i class='icon-magnet'></i>添加新分组</a></li>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>评论</li>
<!--<li class=''><a href="../discuss/index.php"><i class='icon-comment'></i>评论列表</a></li>-->
<li class=''><a href="../problem/comments.php"><i class='icon-comment'></i>评论列表</a></li>
<li class='nav-header'>页面</li>
<li class=''><a href="../page/index.php"><i class='icon-file'></i>页面列表</a></li>
<li class=''><a href="../page/editpage.php?action=add"><i class='icon-edit'></i>添加新页面</a></li>
</ul>
</ul>
<ul class='nav nav-list span2'>
<li class='nav-header'>其他</li>
<li class=''><a href="gen_acrank.php"><i class='icon-retweet'></i>生成等级</a></li>
<li class=''><a href="clear_record.php"><i class='icon-repeat'></i>清理垃圾</a></li>
<li class='nav-header'>系统</li>
<li class=''><a href="settings.php"><i class='icon-cog'></i>参数设置</a></li>
<li class=''><a href="backup.php"><i class='icon-hdd'></i>备份与恢复</a></li>
<li class=''><a href="/phpmyadmin"><i class='icon-music'></i>phpMyAdmin</a></li>
</ul>
</div>
</div>
<?php
include_once("../include/footer.php");
?>
