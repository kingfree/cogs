<?php
require_once("../include/stdhead.php");
gethead(1,"sess","代码");
$LIB->dpshhl();
?>

<?php
$p=new DataAccess();
$sql="select problem.filename,problem.probname,userinfo.uid,userinfo.nickname,userinfo.realname,compscore.ctid,compscore.subtime,compscore.score,compscore.result,compscore.lang from problem,compscore,userinfo where compscore.pid=problem.pid and userinfo.uid=compscore.uid and compscore.csid={$_GET[csid]}";
$cnt=$p->dosql($sql);
if ($cnt) {
	$d=$p->rtnrlt(0);
	if ($d[lang]==0) $ext="pas"; else
	if ($d[lang]==1) $ext="c"; else
	if ($d[lang]==2) $ext="cpp"; 
	$fp=fopen("{$SETTINGS['dir_competition']}{$d[ctid]}/{$d[uid]}/{$d[filename]}.{$ext}","r");
	if (is_resource($fp))
	{
		$code=rfile($fp);
	}
	fclose($fp);
    $code=mb_convert_encoding($code, "utf-8", "gbk");
}
else
{
	echo '<script>document.location="../error.php?id=16"</script>';
}
?>
<table width="100%" border="1">
  <tr>
    <th width="60px" scope="col">CSID</th>
    <td width="100px" scope="col"><?php echo $_GET['csid']; ?></td>
    <th valign="top">代码语言：<?php echo $STR[lang][$d['lang']] ?> </th>
  </tr>
  <tr>
    <th scope="col">题目名</th>
    <td scope="col"><?php echo $d[probname]; ?></td>
<?  if($d['lang']==0) $langstr="pascal";
else if($d['lang']==1) $langstr="c";
else if($d['lang']==2) $langstr="cpp"; ?>
<td rowspan=6>
<pre class="brush: <?=$langstr?>;"><?=htmlspecialchars($code)?></pre>
</td>
  </tr>
  <tr>
    <th scope="col">用户昵称</th>
    <td scope="col"><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['nickname']}</a>"; ?></td>
  </tr>
  <tr>
    <th scope="col">姓名</th>
    <td scope="col"><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['realname']}</a>"; ?></td>
  </tr>
  <tr>
    <th scope="col">得分</th>
    <td scope="col"><?php echo $d['score'] ?></td>
  </tr>
  <tr>
    <th scope="col">测试点</th>
    <td scope="col"><pre style='margin:0;'><?php judgeresult($d['result']) ?></pre></td>
  </tr>
  <tr valign=top>
    <th scope="col">提交时间</th>
    <td scope="col"><?php echo date('Y-m-d H:i:s',$d[subtime]); ?></td>
  </tr>
</table>

<script type="text/javascript">SyntaxHighlighter.all();</script>

<?php
	include_once("../include/stdtail.php");
?>
