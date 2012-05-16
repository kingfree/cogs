<?php
require_once("../include/stdhead.php");
gethead(1,"sess","代码");
$LIB->dpshhl();
?>

<?php
$p=new DataAccess();
$q=new DataAccess();
if(!$_GET['id']) {
    $sql = "select max(sid) as sid from submit";
    $p->dosql($sql);
    $d=$p->rtnrlt(0);
    $_GET['id'] = $d['sid'];
}
$sql="select submit.*,userinfo.nickname,userinfo.realname,submit.subtime,problem.probname,problem.filename from submit,userinfo,problem where submit.pid=problem.pid and submit.uid=userinfo.uid and submit.sid={$_GET['id']}";
$cnt=$p->dosql($sql);
if($cnt) {
    $d=$p->rtnrlt(0);
    $fp=fopen("{$SET['dir_source']}{$d['uid']}/{$d['srcname']}","r");
    if (is_resource($fp))
        $code=rfile($fp);
    fclose($fp);
    if(get_magic_quotes_gpc())
        $code=stripslashes($code);
    $code=mb_convert_encoding($code, "utf-8", "gbk");
} else 异常("提交记录不存在");
?>
<table id="submitdetail">
  <tr>
    <th width="60px">SID</th>
    <td width="100px"><?php echo $d['sid']; ?></td>
    <th>代码语言：<?php echo $STR['lang'][$d['lang']] ?></th>
  </tr>
  <tr>
    <th>题目名称</th>
    <td><a href="pdetail.php?pid=<?php echo $d['pid']; ?>" target="_blank"><?php echo $d['probname']; ?></a></td>
    <td rowspan=9><?php
if(有此权限($q, '查看代码') || $d['uid']==$_SESSION['ID'])
    $forcetocode=1;
else {
    $sql="select showcode from comments where uid={$d['uid']} and pid={$d['pid']}";
    $cnt=$p->dosql($sql);
    if ($cnt) {
        $f=$p->rtnrlt(0);
        $forcetocode=$f['showcode'];
    }

    if(!$forcetocode) {
        $sql = "select accepted from submit where uid={$_SESSION['ID']} and pid={$d['pid']} order by score desc limit 1";
        $cnt=$p->dosql($sql);
        if ($cnt) {
            $f=$p->rtnrlt(0);
            $forcetocode=$f['accepted'];
        }
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
    <th>用户昵称</th>
    <td><a href="../user/detail.php?uid=<?php echo $d['uid']; ?>" target="_blank"><?php echo $d['nickname']; ?></a></td>

  </tr>
  <tr>
    <th>最终得分</th>
    <td><?php echo $d['score'] ?></td>
  </tr>
  <tr>
    <th>评测结果</th>
    <td><pre style='margin:0;'><?php 评测结果($d['result']) ?></pre></td>
  </tr>
  <tr>
    <th>是否通过</th>
    <td><?php echo $d['accepted']?"<span class=ok>通过":"<span class=no>未通过"; ?></span></td>
  </tr>
  <tr>
    <th>运行时间</th>
    <td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
  </tr>
  <tr>
    <th>内存使用</th>
    <td><?php printf("%.2f",$d['memory']/1024) ?> MiB </td>
  </tr>
  <tr>
    <th>提交时间</th>
    <td><?php echo date('Y-m-d H:i:s',$d[subtime]); ?></td>
  </tr>
  <tr>
    <th>重新评测</th>
    <td><form id="act" name="act" method="post" action="../compile/">
        <input name="pid" type="hidden" id="pid" value="<?=$d['pid']; ?>" />
        <input name="sid" type="hidden" id="sid" value="<?=$d['sid']; ?>" />
        <input type="hidden" name="rejudge" value="1">
        <input type="hidden" name="lang" value="<?php echo langnumtostr($d['lang']) ?>">
        <input type="submit" name="Submit" value="Rejudge" class="Button"/>
    </form>    </td>
  </tr>
  <?php if(有此权限($q, '查看用户')) { ?>
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

