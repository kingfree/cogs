<?php
require_once("../include/stdhead.php");
gethead(1,"","比赛");
?>

<?php if ($_SESSION['admin']>0){ ?>
<a class="admin_big" href="../admin/comp/editcompbase.php?action=add">添加新比赛</a>
<?php } ?>
<div id="nowtime">
现在时间：<?=date('Y-m-d H:i:s', time());?>
</div>
<?
$p=new DataAccess();
$sql="select comptime.*,compbase.*,userinfo.realname,groups.* from comptime,compbase,userinfo,groups where comptime.cbid=compbase.cbid and userinfo.uid=compbase.ouid and comptime.group=groups.gid order by starttime desc";
$cnt=$p->dosql($sql);
$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
if(!$_GET['page']) {
    $_GET['page']=1;
    $st=0;
} else {
    if ($_GET[page]<1 || $_GET[page]>$totalpage)
        异常("页面错误！");
    else
        $st=(($_GET[page]-1)*$SET['style_pagesize']);
}
?>
<? 分页($cnt, $_GET['page']); ?>
<table id="contestlist">
<tr>
    <th>比赛</th>
    <th>场次介绍</th>
    <th>状态</th>
    <th>比赛成绩</th>
    <th>开始时间</th>
    <th>结束时间</th>
    <th>开放分组</th>
<? if($_SESSION['admin']) { ?>
    <th class=admin>评测</th>
    <th class=admin>比赛</th>
    <th class=admin>场次</th>
    <th class=admin>权限</th>
    <th class=admin>组织者</th>
<? } ?>
</tr>
<?
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
		$d=$p->rtnrlt($i);
?>
<tr>
    <th><a href='comp.php?ctid=<?=$d[ctid] ?>&uid=<?=$_SESSION['ID'] ?>'><?=$d[cname] ?></a></th>
    <td><?=nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
    <td align=center><?php
	 if (time()>$d[endtime]) echo "<span class='did'>结束</span>"; else
	 if (time()<$d[endtime] && time()>$d[starttime]) echo "<span class='doing'>进行</span>"; else
	 echo "<span class='todo'>准备</span>"; 
	 ?></td>
    <td align=center><?  if ($d[showscore] || $_SESSION[admin])
                echo "<a href='report.php?ctid=$d[ctid]'>查看</a> ";
            if (!$d[showscore])
                echo "未";
     ?></td>
    <td align=center><?=date('Y-m-d H:i', $d[starttime]) ?></td>
    <td align=center><?=date('Y-m-d H:i', $d[endtime]) ?></td>
    <td align=center><a href="../information/userlist.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td>
<? if($_SESSION['admin']) { ?>
<td class=admin align=center><a href="../admin/comp/comptime.php?ctid=<?=$d['ctid']?>">评测</a></td>
<td class=admin align=center><a href="../admin/comp/editcompbase.php?action=edit&cbid=<?=$d['cbid']?>"><?=$d['cbid']?></a></td>
<td class=admin align=center><a href="../admin/comp/editcomptime.php?action=edit&ctid=<?=$d['ctid']?>"><?=$d['ctid']?></a></td>
<td class=admin align=center><?=$d['readforce']?></td>
<td class=admin align=center><a href='../user/detail.php?uid=<?=$d['ouid']?>' target='_blank'><?=$d['realname']?></a></td>
<? } ?>
</tr>
<? } ?>
</table>
<?php
	include_once("../include/stdtail.php");
?>
