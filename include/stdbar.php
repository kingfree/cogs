<div id="bar" style="border:none">
<?php
$p=new DataAccess();
global $STR,$LIB;
$panel=$SETTINGS['base']."user/panel.php";
$index=$SETTINGS['base']."index.php";
$problist=$SETTINGS['base']."problem/problist.php";
$cate=$SETTINGS['base']."information/catelist.php";
$competition=$SETTINGS['base']."competition/index.php";
$help=$SETTINGS['base']."information/help.php";
$about=$SETTINGS['base']."information/about.php";
$mail=$SETTINGS['base']."mail/index.php";
$register=$SETTINGS['base']."user/register.php?accept=1";
$login=$SETTINGS['base']."user/login.php";
$logout=$SETTINGS['base']."user/dologout.php";
$bbs=$SETTINGS['base']."bbs/index.php";
$admin=$SETTINGS['base']."admin/index.php";
$grader=$SETTINGS['base']."information/grader.php";
$submitlist=$SETTINGS['base']."information/submitlist.php";
$userlist=$SETTINGS['base']."information/userlist.php";
$grouplist=$SETTINGS['base']."information/grouplist.php";
$udetail=$SETTINGS['base']."user/detail.php?uid={$_SESSION['ID']}";
$rank=$SETTINGS['base']."information/rank.php";
$verfy=$SETTINGS['base']."user/verfy.php";
$editbulletin=$SETTINGS['base']."admin/settings/editkey.php?sname=global_bulletin&method=text";
?>
<table width=100% border="0" id="nag">
<tr text-align="center">
<td><a href="<?=pathconvert($SETTINGS['cur'],$index);?>"><span class="icon-home"></span>首页</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$problist);?>"><span class="icon-list"></span>题目</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$cate);?>"><span class="icon-th"></span>分类</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$competition);?>"><span class="icon-list-alt"></span>比赛</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$submitlist);?>"><span class="icon-film"></span>记录</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$userlist);?>"><span class="icon-user"></span>用户</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$grouplist);?>"><span class="icon-th-large"></span>分组</a></td>
<? if($_SESSION['ID']) {?>
<td><a href="<?=pathconvert($SETTINGS['cur'],$panel); ?>"><span class="icon-picture"></span>个人</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$mail); ?>"><span class="icon-envelope"></span>信件<?
if($_SESSION['ID']) {
    $uid = $_SESSION['ID'];
    $sql = "select mid from mail where readed = 0 and toid = $uid";
    $cnt = $p->dosql($sql);
    if($cnt > 0)
        echo "<span style='color: #FF0000;'>($cnt)</span>";
}
?></a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$logout);?>"><span class="icon-off"></span>退出</a></td>
<? } else { ?>
<th><a href="<?=pathconvert($SETTINGS['cur'],$login);?>"><span class="icon-star"></span>登录</a></th>
<th><a href="<?=pathconvert($SETTINGS['cur'],$register);?>"><span class="icon-shopping-cart"></span>注册</a></th>
<? } ?>
<?php if ($_SESSION['admin']>0)	{ ?>
<!--<td><a href="<?=pathconvert($SETTINGS['cur'],$grader);?>"><span class="icon-lock"></span>终端</a></td>-->
<td><a style="color: red;" href="<?=pathconvert($SETTINGS['cur'],$admin)?>"><span class="icon-asterisk"></span>后台</a></td>
<? } ?>
<td><a href="<?=pathconvert($SETTINGS['cur'],$help);?>"><span class="icon-question-sign"></span>帮助</a></td>
<td><a href="<?=pathconvert($SETTINGS['cur'],$about);?>"><span class="icon-info-sign"></span>关于</a></td>
<td style="text-align:right;font-size:10pt;"><a href="#" onclick="document.getElementById('trad').innerHTML='正';document.getElementById('alltext').innerHTML = TradSimp.getTrad(document.getElementById('alltext').innerHTML);" id="trad">繁</a>/<a href="#" onclick="document.getElementById('trad').innerHTML='繁';document.getElementById('alltext').innerHTML = TradSimp.getSimp(document.getElementById('alltext').innerHTML);" id="simp">简</a></td>
</tr>
</table>
</div>
