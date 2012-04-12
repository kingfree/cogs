<?php
$p=new DataAccess();
?>
<table id="nagbar">
<tr>
<td><a href="<?=路径("index.php");?>"><span class="icon-home"></span>首页</a></td>
<td><a href="<?=路径("page/index.php");?>"><span class="icon-file"></span>页面</a></td>
<td><a href="<?=路径("problem/problist.php");?>"><span class="icon-list"></span>题目</a></td>
<td><a href="<?=路径("information/catelist.php");?>"><span class="icon-tags"></span>分类</a></td>
<td><a href="<?=路径("information/submitlist.php");?>"><span class="icon-align-justify"></span>记录</a></td>
<td><a href="<?=路径("information/comments.php");?>"><span class="icon-comment"></span>讨论</a></td>
<td><a href="<?=路径("competition/index.php");?>"><span class="icon-list-alt"></span>比赛<?
$now = time();
$cnt2 = $p->dosql("select ctid from comptime where starttime < $now and endtime > $now");
if($cnt2 > 0) echo "<span class='doing'>($cnt2)</span>";
$cnt1 = $p->dosql("select ctid from comptime where starttime > $now and endtime > $now");
if($cnt1 > 0) echo "<span class='todo'>($cnt1)</span>";
?></a></td>
<td><a href="<?=路径("information/userlist.php");?>"><span class="icon-user"></span>用户</a></td>
<td><a href="<?=路径("information/grouplist.php");?>"><span class="icon-th-large"></span>分组</a></td>
<? if($_SESSION['ID']) {?>
<? } else { ?>
<!--<th><a href='javascript:$("#login").show()'><span class="icon-star"></span>登录</a></th>-->
<th><a href='<?=路径("user/login.php")?>?from=<?=$SET['URI']?>'><span class="icon-star"></span>登录</a></th>
<th><a href="<?=路径("user/register.php?accept=1");?>"><span class="icon-shopping-cart"></span>注册</a></th>
<? } ?>
<?php if ($_SESSION['admin']>0)	{ ?>
<td><a class="admin" href="<?=路径("admin/index.php");?>"><span class="icon-asterisk"></span>后台</a></td>
<? } ?>
<td><a href="<?=路径("information/about.php");?>"><span class="icon-info-sign"></span>关于</a></td>
<td style="text-align:right;"><a href="#" onclick="document.getElementById('trad').innerHTML='正';document.getElementById('alltext').innerHTML = TradSimp.getTrad(document.getElementById('alltext').innerHTML);" id="trad">繁</a>/<a href="#" onclick="document.getElementById('trad').innerHTML='繁';document.getElementById('alltext').innerHTML = TradSimp.getSimp(document.getElementById('alltext').innerHTML);" id="simp">简</a></td>
</tr>
</table>
