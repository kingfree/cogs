<?php
require_once("../include/stdhead.php");
gethead(1,"可以管理","后台管理");
    $q=new DataAccess();
?>

<div class='well span2 pull-right'>
<ul class='nav nav-list'>
<li class='nav-header'>题目</li>
<li class=''><a href="../problem/index.php">题目列表</a></li>
<li class=''><a href="problem/editprob.php?action=add">添加新题目</a></li>
<li class='nav-header'>比赛</li>
<li class=''><a href="../competition/index.php">比赛列表</a></li>
<li class=''><a href="settings.php?settings=comp">比赛管理</a></li>
<li class=''><a href="comp/editcompbase.php?action=add">添加新比赛</a></li>
<li class='nav-header'></li>
<li class=''></li>
<li class=''></li>
</ul>
</div>

<table id="admin_index">
<th>比赛</th>
<td><span>
<a href="../competition/index.php">比赛列表</a>
</span>
<span class="admin">
<a href="settings.php?settings=comp">比赛管理</a>
</span></td>
<td><span class="admin">
<a href="comp/editcompbase.php?action=add">添加新比赛</a>
</span></td>
</tr>
<tr>
<th>分类</th>
<td><span>
<a href="../information/catelist.php">分类列表</a>
</span></td>
<td><span class="admin">
<a href="category/editcate.php?action=add">添加新分类</a>
</span></td>
</tr>
<tr>
<th>用户</th>
<td><span>
<a href="../problem/index.php">用户列表</a>
</span><span class="admin">
<a href="settings.php?settings=privilege">权限管理</a>
</span></td>
<td>
</td>
</tr>
<tr>
<th>分组</th>
<td><span>
<a href="../information/grouplist.php">分组列表</a>
</span></td>
<td><span class="admin">
<a href="group/editgroup.php?action=add">添加新分组</a>
</span></td>
</tr>
<tr>
<th>评论</th>
<td><span>
<a href="../information/comments.php">评论列表</a>
</span>
<span class="admin">
<a href="settings.php?settings=comments">评论管理</a>
</span></td>
<td>
</td>
</tr>
<tr>
<th>评测</th>
<td><span>
<a href="../information/grader.php">评测机列表</a>
</span></td>
<td><span class="admin"><a href="../admin/grader/editgrader.php?action=add">添加评测机</a></span></td>
</tr>
<tr>
<th>信息</th>
<td>
<a href="user/loginlog.php">登录记录</a>
<span class="admin"><a href="settings.php?settings=rank">排名管理</a></span>
</td>
<td>
</td>
</tr>
<tr>
<th>系统</th>
<td><? if(有此权限('超级用户')) { ?><span class="admin"><a href="settings.php?settings=settings">参数设置</a></span><? } ?></td>
<td>
</td>
</tr>
<? if(有此权限('超级用户')) { ?><tr>
<th rowspan=2>高级</th>
<td><span><a href="settings.php?settings=dbctrl">数据库管理</a></span></td>
<td><span><a href="settings.php?settings=terminal">PHP终端访问</a></span></td>
</tr>
<tr>
<td><span><a href="settings.php?settings=serverinfo">服务器信息</a></span></td>
<td><span><a href="settings.php?settings=phpinfo">PHP信息</a></span></td>
</tr><? } ?>
</table>
<?php
include_once("../include/stdtail.php");
?>
