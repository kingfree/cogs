<?php
$problist=$SET['base']."problem/index.php";

$sql="select * from category order by cname";
$cnt=$p->dosql($sql);
if ($cnt)
{
	$table_width=14;
?>
<p>
<table border="1" id="cat">
	<tr>
<?php
	$last=0;
	$linecnt=0;
	$line=1;
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		$last=$d['pid'];
		$linecnt++;
?>
		<td><a href="<?php echo pathconvert($SET['cur'],$problist)."?caid=".$d['caid'] ?>"><?php echo $d['cname'] ?></a></td>
<?php
		if ($linecnt==$table_width)
		{
			$linecnt=0;
			$line++;
?>
	</tr>
	<tr>
<?php
		}
	}
if ($linecnt>0 && $line>1)
{
	for ($i=$linecnt;$i<$table_width;$i++)
	{
?>
		<td>&nbsp;</td>
<?php
	}
}
?>
	</tr>
</table></p>
<?php
}
?>
