<?php
require_once("../include/stdhead.php");
gethead(1,"","查看帖子");
$did= (int) $_GET['did'];
if(!$did) {
echo '<script>document.location="index.php"</script>';
}
$p = new DataAccess();
$q = new DataAccess();
$sql = "select discuss.*,problem.probname,userinfo.nickname,userinfo.email from discuss,problem,userinfo where (fid=$did or did=$did) and discuss.pid=problem.pid and userinfo.uid=discuss.uid order by did asc";
$cnt = $p->dosql($sql);
$page = (int) $_GET['page'];
$st = $page ? (($page-1)*$SET['style_pagesize']) : 0;
$d=$p->rtnrlt(0);
$pid = $d['pid'];
?>
<table id='forum' width=100% border=1>
<tr style="background-color: Gainsboro; font-size: 18px;">
<td colspan=3><b>
<?=$d['title']?>
</b></td>
<td align=center colspan=3>
<a href="../problem/problem.php?pid=<?=$pid?>"><?=$d['probname']?></a>
</td>
</tr>
<?
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'];$i++) {
$d=$p->rtnrlt($i);
?>
<tr>
<td width="260px" rowspan=2><?php @showuser($d['uid'],$q) ?></td>
<td height=70px colspan=5 valign=top style="font-size:16px; padding:8px;"><?=$d['text']?></td>
</tr>
<tr border=0>
<td><? if($d['code']) { ?><a href="../problem/code.php?id=<?=$d['code']?>">关联代码</a><? } ?>&nbsp;</td>
<th width=50px><?=$i+1?>楼</td>
<td width=160px align=center><?=date('Y-m-d H:i:s',$d['time'])?></td>
<td width="60px" align=center>顶 <?=($d['up'])?></td>
<td width="60px" align=center>踩 <?=($d['down'])?></td>
</tr>
<?
}
?>
</table>

<form id=huifu name=huifu method=post action=huifu.php>
<textarea name="text" cols="100" rows="8" id="text" class="TextArea">
</textarea>
<br />
<input name="submit" type="submit" id="submit" value="发表回复" class="LinkButton"/>
<input name="showcode" type="checkbox" id="showcode" value="1" <?php if ($sc){ ?> checked="checked" <?php } ?> /><label for="showcode">关联你最近提交的代码</label>
<input name="fid" type="hidden" id="fid" value="<?=$did ?>" />
<input name="pid" type="hidden" id="pid" value="<?=$pid ?>" />
<input name="url" type="hidden" id="url" value="<?=$_SERVER['PHP_SELF']?>" />
</form>
<?
page_slice($cnt,$page);
?>
<?
require_once("../include/stdtail.php");
?>
