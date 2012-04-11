<?php
$sql="select * from submit,userinfo where submit.pid={$pid} and submit.uid=userinfo.uid and submit.accepted>0 order by runtime asc,subtime asc";
$cnt=$p->dosql($sql);
$lastuid=0;
for ($i=0;$i<$cnt && $i<$SET['style_single_ranksize'];$i++) {
    $d=$p->rtnrlt($i);
    if ($d['uid']==$lastuid)
        continue;
    $lastuid=$d['uid'];
?>
<tr>
<td><a href="../user/detail.php?uid=<?=$d['uid'] ?>"><?=gravatar::showImage($d['email']);?><?=$d['nickname'] ?></a></td>
<td align=right><?php printf("%.3f s",$d['runtime']/1000.0) ?></td>
<td align=center><a href="submitdetail.php?id=<?=$d['sid'] ?>" target="_blank"><?=$STR['lang'][$d['lang']]?></a></td>
</tr>
<?php 
}
?>
