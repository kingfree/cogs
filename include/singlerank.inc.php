<table border="0" width="100%">
<?php
$sql="select * from submit,userinfo where submit.pid={$pid} and submit.uid=userinfo.uid and submit.accepted>0 and lang={$lang} order by runtime asc,subtime asc";
$cnt=$p->dosql($sql);
$lastuid=0;
for ($i=0;$i<$cnt && $i<$SETTINGS['style_single_ranksize'];$i++)
{
	$d=$p->rtnrlt($i);
	if ($d['uid']==$lastuid)
		continue;
	$lastuid=$d['uid'];
?>
        <tr>
          <td>
<?=gravatar::showImage($d['email']);?>
<a href="../user/detail.php?uid=<?php echo $d['uid'] ?>"><?php echo $d['nickname'] ?></a></td>
          <td align=center><?php printf("%.3f s",$d['runtime']/1000.0) ?></td>
          <td align=center><a href="submitdetail.php?id=<?php echo $d['sid'] ?>" target="_blank">查看</a></td>
        </tr>
<?php 
}
?>
</table>
