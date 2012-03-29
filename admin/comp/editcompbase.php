<?php
require_once("../../include/stdhead.php");
gethead(1,"admin","修改比赛");
?>

<body>
<a href="../settings.php?settings=comp">比赛基本管理</a>
<?php
$p=new DataAccess();
if ($_GET[action]=='edit')
{
	$sql="select * from compbase where cbid={$_GET[cbid]} ";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$pbs=explode(":",$d['contains']);
	$pbs=array_flip($pbs);
}
?>
<form id="form1" name="form1" method="post" action="doeditcompbase.php?action=<?php echo $_GET[action] ?>&cbid=<?php echo $_GET[cbid]; ?>">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td>CBID</td>
    <td><?php echo $d[cbid] ?></td>
  </tr>
  <tr>
    <td>比赛名</td>
    <td>
      <input name="cname" type="text" class="InputBox" id="cname" value="<?php echo $d[cname] ?>" /></td>
  </tr>
  <tr>
    <td>关联题目</td>
    <td>
	<table border="1">
      <!--<tr>
        <th scope="col">选择</th>
        <th scope="col">题目名</th>
      </tr>-->
	  
	<tr>
	<?php
	$sql="select pid,probname from problem order by pid";
	$cnt=$p->dosql($sql);
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
        if($i % 6 == 0) echo "</tr><tr>";
?>
      <td><input name="cons[]" type="checkbox" id="cons[]" value="<?php echo $d[pid] ?>"  <?php if (is_numeric($pbs[$d[pid]])) echo 'checked="checked"' ?> /><?=$d[pid]?>.<a href="../../problem/pdetail.php?pid=<?php echo $d[pid] ?>" target="_blank"><?php echo $d[probname] ?></a></td>
<?php
	}
?>
	</tr>
</table></td>
  </tr>
</table>
<p>
  <input type="submit" name="Submit" value="提交修改"  class="Button" />
</form>

<?php
	include_once("../../include/stdtail.php");
?>
