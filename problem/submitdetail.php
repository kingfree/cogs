<?php
require_once("../include/stdhead.php");
gethead(1,"sess","提交详细记录");
$LIB->dpshhl();
?>

<?php
$p=new DataAccess();
$q=new DataAccess();
$sql="select * from code where sid={$_GET['id']}";
$hascode=$p->dosql($sql);
$r=$p->rtnrlt(0);
$code = $r['code'];
$sql="select submit.*,userinfo.nickname,userinfo.realname,submit.subtime,problem.probname,problem.filename from code,submit,userinfo,problem where submit.pid=problem.pid and submit.uid=userinfo.uid and submit.sid={$_GET['id']}";
$cnt=$p->dosql($sql);
if($cnt) {
    $d=$p->rtnrlt(0);
    if(!$hascode) {
        $fp=fopen("{$SETTINGS['dir_source']}{$d['uid']}/{$d['srcname']}","r");
        if (is_resource($fp))
            $code=rfile($fp);
        fclose($fp);
        if(get_magic_quotes_gpc())
            $code=stripslashes($code);
        $source=mysql_real_escape_string($code);
        $sql1="INSERT INTO `code`(`sid`,`code`)VALUES('{$_GET['id']}','$source')";
        $ok=$q->dosql($sql1);
    }
} else {
    echo '<script>document.location="../error.php?id=16"</script>';
}
?>
<table width="100%" border="1" bordercolor=#000000 cellspacing=0 cellpadding=4>
  <tr>
    <th width="60px" scope="col">SID</th>
    <td width=100px scope="col"><?php echo $d['sid']; ?></td>
    <th>代码语言：<?php echo $STR['lang'][$d['lang']] ?></th>
  </tr>
  <tr>
    <th scope="col">题目</th>
    <td scope="col"><a href="pdetail.php?pid=<?php echo $d['pid']; ?>" target="_blank"><?php echo $d['probname']; ?></a></td>
    <td rowspan=9><?php
if ($_SESSION['admin']>0 || $d['uid']==$_SESSION['ID'])
    $forcetocode=1;
else {
    $sql="select code from discuss where code={$d['sid']}";
    $cnt=$p->dosql($sql);
    if ($cnt) {
        $f=$p->rtnrlt(0);
        $forcetocode=$f['code'];
    }
}
if ($forcetocode) {
    if($d['lang']==0) $langstr="pascal";
    else if($d['lang']==1) $langstr="c";
    else if($d['lang']==2) $langstr="cpp";
?>
<pre class="brush: <?=$langstr?>;"><?=htmlspecialchars($code)?></pre>
<?php } else {
?>
    <h1>您没有权限查看代码。</h1>
<?php } ?>
</td>  </tr>
  <tr>
    <th scope="col">用户</th>
    <td scope="col"><a href="../user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?php echo $d['nickname']; ?></a></td>

  </tr>
  <tr>
    <th scope="col">得分</th>
    <td scope="col"><?php echo $d['score'] ?></td>
  </tr>
  <tr>
    <th scope="col">测试点</th>
    <td scope="col"><pre style='margin:0;'><?php judgeresult($d['result']) ?></pre></td>
  </tr>
  <tr>
    <th scope="col">状态</th>
    <td scope="col"><?php echo $d['accepted']?"通过":"未通过"; ?></td>
  </tr>
  <tr>
    <th scope="col">耗时</th>
    <td scope="col"><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
  </tr>
  <tr>
    <th scope="col">内存使用</th>
    <td scope="col"><?php printf("%.2f",$d['memory']/1024) ?> MiB </td>
  </tr>
  <tr>
    <th scope="col">提交时间</th>
    <td scope="col"><?php echo date('Y-m-d H:i:s',$d[subtime]); ?></td>
  </tr>
  <tr valign=top>
    <th scope="col">重新评测</th>
    <td scope="col"><form id="act" name="act" method="post" action="../compile/">
        <input name="pid" type="hidden" id="pid" value="<?php echo  $d['pid']; ?>" />
        <input name="sid" type="hidden" id="sid" value="<?php echo  $d['sid']; ?>" />
        <input type="hidden" name="rejudge" value="1">
        <input type="hidden" name="lang" value="<?php echo langnumtostr($d['lang']) ?>">
        <input type="submit" name="Submit" value="Rejudge" class="Button"/>
    </form>    </td>
  </tr>
  <?php if ($_SESSION['admin']>0){ ?>
  <tr class=admin>
    <th>IP</th>
    <td colspan=2><?php echo $d['IP'] ?></td>
  </tr>
  <?php } ?>
</table>

<script type="text/javascript">SyntaxHighlighter.all();</script>
<?php
    include_once("../include/stdtail.php");
?>

