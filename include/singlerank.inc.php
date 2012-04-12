<?php
$sql="select submit.*,userinfo.nickname,userinfo.uid,userinfo.email from submit,userinfo where submit.pid={$pid} and submit.uid=userinfo.uid order by score desc, runtime asc, memory asc limit {$SET['style_single_ranksize']}";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
?>
<tr>
<td><a href="../user/detail.php?uid=<?=$d['uid'] ?>"><?=gravatar::showImage($d['email']);?><?=$d['nickname'] ?></a></td>
<td align=center><span class="<?=$d['accepted']?'ok':'no'?>"><?=$d['score'] ?></span></td>
<td align=right><?php printf("%.3f s",$d['runtime']/1000.0) ?></td>
<td align=center><a href="submitdetail.php?id=<?=$d['sid'] ?>" target="_blank"><?=$STR['lang'][$d['lang']]?></a></td>
</tr>
<?php 
}
?>
