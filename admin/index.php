<?php
require_once("../include/stdhead.php");
gethead(1,"admin","后台管理");
?>
<center>
<h3>后台管理界面</h3>

<table border="1" bordercolor=#000000 cellspacing=0 cellpadding=4>
<tr>
<th scope="col">比赛管理</th>
<td scope="col"><table border="0">
<tr>
<td><span style="font-size:20pt;" class="LinkButton">
<a href="settings.php?settings=comp">比赛管理</a>
</span></td>
<td><span style="font-size:20pt;" class="LinkButton">
<a href="comp/editcompbase.php?action=add">添加新比赛</a>
</span></td>
</tr>
</table>        </td>
</tr>
<tr>
<th scope="col">记录管理</th>
<td scope="col"><table border="0">
<tr>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=rank">排名管理</a></span></td>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=comments"><?php echo $STR[admin][comments] ?></a></span></td>
</tr>
</table></td>
</tr>
<tr>
<th scope="col">高级管理</th>
<td scope="col"><table border="0">
<tr>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=settings">参数设置</a></span></td>
<td><span style="font-size:20pt;" class="LinkButton">
<a href="../information/grader.php">终端查看</a>
</span></td>
</tr>
<tr>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=dbctrl"><?php echo $STR[admin][data] ?></a></span></td>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=serverinfo"><?php echo $STR[admin]['serverinfo'] ?></a></span></td>
</tr>
<tr>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=phpinfo"><?php echo $STR[admin]['phpinfo'] ?></a></span></td>
<td><span style="font-size:20pt;" class="LinkButton"><a href="settings.php?settings=terminal"><?php echo $STR[admin]['terminal'] ?></a></span></td>
</tr>
</table>	</td>
</tr>
</table>
</center>
<?php
include_once("../include/stdtail.php");
?>
