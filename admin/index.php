<?php
require_once("../include/stdhead.php");
gethead(1,"admin","后台管理");
?>
<center>
<h3>后台管理界面</h3>

<table border="1" bordercolor=#000000>
<tr>
<th>题目管理</th>
<td></td>
<td><span style="font-size:20pt;" class="admin">
<a href="problem/editprob.php?action=add">添加新题目</a>
</span></td>
</tr>
<tr>
<th>比赛管理</th>
<td><span style="font-size:20pt;">
<a href="settings.php?settings=comp">比赛管理</a>
</span></td>
<td><span style="font-size:20pt;" class="admin">
<a href="comp/editcompbase.php?action=add">添加新比赛</a>
</span></td>
</tr>
<tr>
<th>记录管理</th>
<td><span style="font-size:20pt;"><a href="settings.php?settings=rank">排名管理</a></span></td>
<td></td>
</tr>
<tr>
<th>分类管理</th>
<td></td>
<td><span style="font-size:20pt;" class="admin">
<a href="category/editcate.php?action=add">添加新分类</a>
</span></td>
</tr>
<tr>
<th>分组管理</th>
<td></td>
<td><span style="font-size:20pt;" class="admin">
<a href="group/editgroup.php?action=add">添加新分组</a>
</span></td>
</tr>
<tr>
<th>评论管理</th>
<td><span style="font-size:20pt;"><a href="settings.php?settings=comments"><?php echo $STR[admin][comments] ?></a></span></td>
<td></td>
</tr>
<tr>
<th>评测机设置</th>
<td><span style="font-size:20pt;">
<a href="../information/grader.php">终端查看</a>
</span></td>
<td><a style="font-size:20pt;" class="admin" href="../admin/grader/editgrader.php?action=add">添加新评测机</a></td>
</tr>
<tr>
<th>系统设置</th>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=settings">参数设置</a></span></td>
<td></td>
</tr>
<tr>
<th rowspan=2>高级管理</th>
<td><span style="font-size:20pt;"><a href="settings.php?settings=dbctrl"><?php echo $STR[admin][data] ?></a></span></td>
<td><span style="font-size:20pt;"><a href="settings.php?settings=serverinfo"><?php echo $STR[admin]['serverinfo'] ?></a></span></td>
</tr>
<tr>
<td><span style="font-size:20pt;"><a href="settings.php?settings=phpinfo"><?php echo $STR[admin]['phpinfo'] ?></a></span></td>
<td><span style="font-size:20pt;"><a href="settings.php?settings=terminal"><?php echo $STR[admin]['terminal'] ?></a></span></td>
</tr>
</table>
</center>
<?php
include_once("../include/stdtail.php");
?>
