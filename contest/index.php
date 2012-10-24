<?php
require_once("../include/header.php");
gethead(1,"","比赛列表");
$p=new DataAccess();
$q=new DataAccess();
?>
<div class='row-fluid'>
<?php if(有此权限('修改比赛')) { ?>
<a href="editcompbase.php?action=add" class="btn btn-info pull-left">添加新比赛</a>
<a href="compbase.php?action=add" class="btn btn-info pull-left">比赛场次管理</a>
<?php } ?>
<a href="recent.php" class='btn btn-success'><i class="icon-list-alt icon-white"></i>最近在线竞赛</a>
<?
$sql="select comptime.*,compbase.*,userinfo.realname,userinfo.nickname,groups.* from comptime,compbase,userinfo,groups where comptime.readforce<={$_SESSION['readforce']} and comptime.cbid=compbase.cbid and userinfo.uid=compbase.ouid and comptime.group=groups.gid order by starttime desc";
$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<div id="nowtime" class='alert alert-success pull-right'>
现在时间：<?=date('Y-m-d H:i:s', time());?>
</div>
<table id="contestlist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
    <th style="width: 10em;">比赛</th>
    <th>场次介绍</th>
    <th style="width: 5ex;">状态</th>
    <th style="width: 5ex;">成绩</th>
    <th style="width: 16ex;">开始时间</th>
    <th style="width: 16ex;">结束时间</th>
    <th style="width: 5em;">开放分组</th>
    <th style="width: 4em;">组织者</th>
<?php if(有此权限('查看比赛')) { ?>
    <th class=admin style="width: 5ex;">评测</th>
<? } ?>
<?php if(有此权限('修改比赛')) { ?>
    <th class=admin style="width: 5ex;">比赛</th>
    <th class=admin style="width: 5ex;">场次</th>
<? } ?>
</tr></thead>
<?
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
		$d=$p->rtnrlt($i);
?>
<tr>
    <td><b><a href='problem.php?ctid=<?=$d[ctid] ?>'><?=$d[cname] ?></a></b></td>
    <td><?=nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
    <td ><?php
	 if (time()>$d[endtime]) echo "<span class='did'>结束</span>"; else
	 if (time()<$d[endtime] && time()>$d[starttime]) echo "<span class='doing'>进行</span>"; else
	 echo "<span class='todo'>准备</span>"; 
	 ?></td>
    <td ><?  if ($d[showscore] || $_SESSION[admin])
                echo "<a href='report.php?ctid=$d[ctid]'>查看</a> ";
            if (!$d[showscore])
                echo "未";
     ?></td>
    <td ><?=date('Y-m-d H:i', $d[starttime]) ?></td>
    <td ><?=date('Y-m-d H:i', $d[endtime]) ?></td>
    <td ><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td>
<td><a href='../user/detail.php?uid=<?=$d['ouid']?>' target='_blank'>
<? if(有此权限("查看用户")) echo $d['realname']; else echo $d['nickname'];?>
</a></td>
<?php if(有此权限('查看比赛')) { ?>
<td><a href="comptime.php?ctid=<?=$d['ctid']?>">评测</a></td>
<? } ?>
<?php if(有此权限('修改比赛')) { ?>
<td><a href="editcompbase.php?action=edit&cbid=<?=$d['cbid']?>"><?=$d['cbid']?></a></td>
<td><a href="editcomptime.php?action=edit&ctid=<?=$d['ctid']?>"><?=$d['ctid']?></a></td>
<? } ?>
</tr>
<? } ?>
</table>
<? 分页($cnt, $_GET['page']); ?>
</div>
<?php
	include_once("../include/footer.php");
?>
