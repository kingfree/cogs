<?php
require_once("../include/stdhead.php");
gethead(1,"","比赛");
$p=new DataAccess();
$q=new DataAccess();
?>

<?php if(有此权限('修改比赛')) { ?>
<a href="../admin/comp/editcompbase.php?action=add" class="btn btn-info pull-left">添加新比赛</a>
<?php } ?>
<?
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
<div id="nowtime" class='alert alert-success pull-right'>
现在时间：<?=date('Y-m-d H:i:s', time());?>
</div>
<? 分页($cnt, $_GET['page']); ?>
<table id="contestlist" class='table table-condensed fixed'>
<tr>
    <th width='140px'>比赛</th>
    <th>场次介绍</th>
    <th width='40px'>状态</th>
    <th width='40px'>成绩</th>
    <th width='100px'>开始时间</th>
    <th width='100px'>结束时间</th>
    <th width='140px'>开放分组</th>
<?php if(有此权限('查看比赛')) { ?>
    <th class=admin width='40px'>评测</th>
    <th class=admin width='60px'>组织者</th>
    <th class=admin width='40px'>权限</th>
<? } ?>
<?php if(有此权限('修改比赛')) { ?>
    <th class=admin width='40px'>比赛</th>
    <th class=admin width='40px'>场次</th>
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
    <td align=center><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td>
<?php if(有此权限('查看比赛')) { ?>
<td class=admin align=center><a href="../admin/comp/comptime.php?ctid=<?=$d['ctid']?>">评测</a></td>
<td class=admin align=center><a href='../user/detail.php?uid=<?=$d['ouid']?>' target='_blank'><?=$d['realname']?></a></td>
<td class=admin align=center><?=$d['readforce']?></td>
<? } ?>
<?php if(有此权限('修改比赛')) { ?>
<td class=admin align=center><a href="../admin/comp/editcompbase.php?action=edit&cbid=<?=$d['cbid']?>"><?=$d['cbid']?></a></td>
<td class=admin align=center><a href="../admin/comp/editcomptime.php?action=edit&ctid=<?=$d['ctid']?>"><?=$d['ctid']?></a></td>
<? } ?>
</tr>
<? } ?>
</table>
<?php
	include_once("../include/stdtail.php");
?>
