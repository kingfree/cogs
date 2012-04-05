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
	$fp=fopen("{$SET['dir_competition']}{$d[ctid]}/{$d[uid]}/{$d[filename]}.{$ext}","r");
	if (is_resource($fp)) {
		$code=rfile($fp);
	}
	fclose($fp);
    $code=mb_convert_encoding($code, "utf-8", "gbk");
} else 异常("提交记录不存在");

?>
<table id="submitdetail">
  <tr>
    <th width="60px">CSID</th>
    <td width="100px"><?=$_GET['csid']; ?></td>
    <th valign="top">代码语言：<?=$STR[lang][$d['lang']] ?> </th>
  </tr>
  <tr>
    <th>题目名称</th>
    <td><?=$d[probname]; ?></td>
<?  if($d['lang']==0) $langstr="pascal";
else if($d['lang']==1) $langstr="c";
else if($d['lang']==2) $langstr="cpp"; ?>
<td rowspan=7>
<pre class="brush: <?=$langstr?>;"><?=htmlspecialchars($code)?></pre>
</td>
  </tr>
  <tr>
    <th>用户昵称</th>
    <td><?="<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['nickname']}</a>"; ?></td>
  </tr>
  <tr>
    <th>真实姓名</th>
    <td><?="<a href='../user/detail.php?uid={$d[uid]}' target='_blank'>{$d['realname']}</a>"; ?></td>
  </tr>
  <tr>
    <th>最终得分</th>
    <td><?=$d['score'] ?></td>
  </tr>
  <tr>
    <th>评测结果</th>
    <td><pre style='margin:0;'><?php 评测结果($d['result']) ?></pre></td>
  </tr>
  <tr valign=top>
    <th>提交时间</th>
    <td><?=date('Y-m-d H:i:s',$d[subtime]); ?></td>
  </tr>
</table>

<script type="text/javascript">SyntaxHighlighter.all();</script>

<?php
	include_once("../include/stdtail.php");
?>
