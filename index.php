<?php
require_once("./include/stdhead.php");
gethead(1,"","首页");
$p=new DataAccess();
$q=new DataAccess();
?>

<table width="100%" class="MainText">
<tr>
<td valign="top" width="160px">
<table width=100% border="0">
<tr><td align=center>
题目快捷方式
</td></tr>
<tr><td align=center>
<form id="form1" name="form1" method="get" action="problem/problist.php">
<input name="key" type="text" id="key" class="InputBox" size="10"/>
<input class="icon-search LinkButton" name="sc" type="submit" id="sc" value=""/>
</form>
</td></tr>
<tr><td align=center>
<form action="problem/pdetail.php" method="get" name="gotoprob" target="_blank" id="gotoprob">
<input name="pid" type="text" class="InputBox" id="pid" size="6" />
<input class="LinkButton" type="submit" name="Submit" value="进入" class="Button" />
</form>
</td></tr>
<tr><td align=center>
<a class="LinkButton" href="problem/random.php" title="随机选择一道你没有通过的题目" target="_blank">随机题目<span class="icon-random"></span></a>
</td></tr>
</table>
<br />
<table width=100% border="1">
<tr>
<th style="width:15%;">PID</th>
<th style="width:80%;">题目</th>
</tr>
<?php 
$sql="select * from problem where submitable=1 order by pid desc limit 16";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++)
{
$d=$p->rtnrlt($i);
?>
<tr>
<td><?php echo $d['pid']; ?></td>
<td>
<?
    if ($_SESSION['ID']) {
        $sql="SELECT * FROM submit WHERE pid ={$d['pid']} AND uid ={$_SESSION['ID']} order by accepted desc limit 1";
        $ac=$q->dosql($sql);
        if ($ac) {
            $e=$q->rtnrlt(0);
            if ($e['accepted'])
                echo "<img src='images/sign/right.gif' border='0' />";
            else echo "<img src='images/sign/error.gif' border='0' />";
        } else echo "<img src='images/sign/todo.gif' border='0' />";
    }
?>
<a href="problem/pdetail.php?pid=<?php echo $d['pid']; ?>" target="_blank"><?php echo $d['probname']; ?></a></td>
</tr>
<?php } ?>
</table>
</td>
<td valign="top" >
<?php echo output_text($SETTINGS['global_index']); ?>
</td>
<td width="220px">
<center>
等级Top <?php echo $SETTINGS['style_ranksize']; ?>
<table width=100% border="1">
<tr>
<th></th>
<th>用户</th>
<th>等级</th>
<th>通过</th>
</tr>
<?php 
$sql="select * from userinfo order by grade desc limit 0, {$SETTINGS['style_ranksize']}";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td style="text-align:center;"><?php echo $i+1 ?></td>
<td style="text-align:left">
<a href="user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank">
<?=gravatar::showImage($d['email'], 28);?>
<?php echo $d['nickname']; ?>
</a>
</td>
<td><strong><?php echo $d['grade'] ?></strong></td>
<td><strong><?php echo $d['accepted'] ?></strong></td>
</tr>
<?php } ?>
</table>
</center>
</td>
<td width="220px">
<center>
等级Top <?php echo $SETTINGS['style_ranksize']*2; ?>
<table width=100% border="1">
<tr>
<th>&nbsp;</th>
<th>用户</th>
<th>等级</th>
<th>通过</th>
</tr>
<?php 
$sql="select * from userinfo order by grade desc limit {$SETTINGS['style_ranksize']}, {$SETTINGS['style_ranksize']}";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++)
{
$d=$p->rtnrlt($i);
?>
<tr>
<td style="text-align:center;"><?php echo $SETTINGS['style_ranksize']+$i+1 ?></td>
<td style="text-align:left">
<a class="useri" href="user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank">
<? echo gravatar::showImage($d['email'], 28);?>
<?php echo $d['nickname']; ?>
</a>
</td>
<td><strong><?php echo $d['grade'] ?></strong></td>
<td><strong><?php echo $d['accepted'] ?></strong></td>
</tr>
<?php } ?>
</table>
</center>
</td>
</table>
<?php
require_once("./include/stdtail.php");
?>
