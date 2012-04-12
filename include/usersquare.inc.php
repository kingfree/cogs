<?php
function showuser($uid,$user_access) {
	global $SET,$STR;
	if((int)$uid) {
		$sql="select * from userinfo,groups where uid='{$uid}' and groups.gid=userinfo.gbelong";
		$user_access->dosql($sql);
		$d=$user_access->rtnrlt(0);
	
?>
<table id="userinfo">
<tr>
<td rowspan=3><a href="<?php echo 路径("user/detail.php?uid={$uid}");?>"><?=gravatar::showImage($d['email'], 64);?></a></td>
<td style="font-size:18px;"><b><?php echo $d['nickname'];?></b></td>
<td><a href="<?=路径("mail/index.php"); ?>"><span class="icon-envelope"></span>信件<?
$cnt1 = $user_access->dosql("select mid from mail where readed = 0 and toid = {$uid}");
if($cnt1 > 0) echo "<span class='doing'>($cnt1)</span>";
$cnt1 = $user_access->dosql("select mid from mail where readed = 0 and fromid = {$uid}");
if($cnt1 > 0) echo "<span class='todo'>($cnt1)</span>";
?></a></td>
</tr>
<tr>
<td>通过: <?php echo $d['accepted'] ?>/<?php echo $d['submited'] ?></td>
<td><a href="<?=路径("user/panel.php"); ?>"><span class="icon-cog"></span>设置</a></td>
</tr>
<tr>
<td>等级: <?php echo $d['grade'] ?></td>
<td><a href="<?=路径("user/dologout.php")."?from=".$SET['URI'];?>"><span class="icon-off"></span>退出</a></td>
</tr>
</table>
<? } else { ?>
<table id="userinfo">
<tr>
<td rowspan=3><a href="<?=路径("user/login.php")."?from=".$SET['URI'];?>"><?=gravatar::showImage("", 64);?></a></td>
<!--<th><a href='javascript:$("#login").show()'>登录</a></th>-->
<th><a href='<?=路径("user/login.php")?>?from=<?=$SET['URI']?>'>登录</a></th>
</tr>
<tr>
<td align="center">
<a href="<?=路径("user/lost.php")?>">忘记密码</a></td>
</tr>
<tr>
<th><a href="<?=路径("user/register.php")."?from=".$SET['URI'];?>">注册</a></th>
</tr>
</table>
<?php
	}
}
?>
