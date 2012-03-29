<?php
require_once("../include/stdhead.php");
gethead(1,"sess","代码");
$LIB->dpshhl();
?>

<?php
$p=new DataAccess();
$sql="select problem.filename,problem.probname,userinfo.uid,userinfo.nickname,userinfo.realname,compscore.ctid,compscore.subtime,compscore.score,compscore.result,compscore.lang from problem,compscore,userinfo where compscore.pid=problem.pid and userinfo.uid=compscore.uid and compscore.csid={$_GET[csid]}";
$cnt=$p->dosql($sql);
if ($cnt)
{
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
}
else
{
	echo '<script>document.location="../error.php?id=16"</script>';
}
?>
<table width="100%" border="1" bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <th width="10%" scope="col">CSID</th>
    <td width="90%" scope="col"><?php echo $_GET['csid']; ?></td>
  </tr>
  <tr>
    <th scope="col">题目名</th>
    <td scope="col"><?php echo $d[probname]; ?></td>
  </tr>
  <tr>
    <th scope="col">用户昵称</th>
    <td scope="col"><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['nickname']}</a>"; ?></td>
  </tr>
<?php if ($_SESSION['admin']>0) { ?>
  <tr>
    <th bgcolor="#99FFCC" scope="col">真实姓名</th>
    <td bgcolor="#99FFCC" scope="col"><?php echo "<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['realname']}</a>"; ?></td>
  </tr>
<?php } ?>
  <tr>
    <th scope="col">得分</th>
    <td scope="col"><?php echo $d['score'] ?></td>
  </tr>
  <tr>
    <th scope="col">测试点</th>
    <td scope="col"><?php echo $d['result'] ?></td>
  </tr>
  <tr>
    <th scope="col">提交时间</th>
    <td scope="col"><?php echo date('Y-m-d H:i:s',$d[subtime]); ?></td>
  </tr>
  <tr>
    <th valign="top">代码</th>
    <td>	<p>语言：<?php echo $STR[lang][$d['lang']] ?> </p>
<?  if($d['lang']==0) $langstr="delphi";
    if($d['lang']==1) $langstr="cpp";
    if($d['lang']==2) $langstr="cpp";
?><pre><code class=<?=$langstr?>><?=htmlspecialchars($code)?></code></pre>
  </tr>
</table>

<script class="javascript">
dp.SyntaxHighlighter.HighlightAll('code');
</script>


<?php
	include_once("../include/stdtail.php");
?>
