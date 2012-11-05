<?php
require_once("../include/header.php");
gethead(1,"","记录列表", $_GET['uid']);

$p=new DataAccess();
$q=new DataAccess();
?>
<div class='row-fluid'>
<a class="btn btn-success pull-left" href="graderlist.php">评测机列表</a>
<form id="search_submit" method="get" class='form-search center'>
<span>
用户UID
<input name="uid" type="number" value="<?=$_GET['uid']?>" class='span1' />
题目PID
<input name="pid" type="number" value="<?=$_GET['pid']?>" class='span1' />
<input name="show" type="hidden" value="<?=$_GET['show']?>" />
<input name="display" type="hidden" value="<?=$_GET['display']?>" />
<button type="submit" class='btn btn-primary'>检索</button>
</span>
<span class='btn-group pull-right'>
<button name='display' value='all' class='btn'>全部</button>
<button name='display' value='ac' class='btn'>通过</button>
</span>
<button name='show' value='yes' class='btn btn-inverse'>显示分页记录</button>
</form>
<?php
$sql="select submit.*,userinfo.nickname,userinfo.email,userinfo.realname,problem.probname from submit,userinfo,problem where problem.readforce<={$_SESSION['readforce']} and submit.pid=problem.pid and submit.uid=userinfo.uid ";
if ($_GET['display']=='ac')
    $sql.=" and submit.accepted=1 ";
if ($_GET['uid'])
    $sql.=" and submit.uid={$_GET['uid']} ";
if ($_GET['pid'])
    $sql.=" and submit.pid={$_GET['pid']} ";
$sql.=" order by submit.sid desc";
$limitt=(int)$SET['style_pagesize']/2;
if(!$_GET['show'] && !$_GET['pid']) $sql .= " limit {$limitt}";
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
<table id="submitlist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th style="width: 6ex;">SID</th>
<th>题目</th>
<th>用户</th>
<th style="width: 20ex;">结果</th>
<th style="width: 5ex;">得分</th>
<th style="width: 6ex;">语言</th>
<th style="width: 10ex;">用时</th>
<th style="width: 10ex;">内存</th>
<th style="width: 20ex;">时间</th>
</tr></thead>
<?php if (!$err)
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=$p->rtnrlt($i);
?>
<tr>
<td><a href='code.php?id=<?=$d['sid']?>'><?=$d['sid']?></a></td>
<td><?php if(!$_GET['pid']) {
    是否通过($d['pid'], $q);
    echo "<a href='?pid={$d['pid']}&uid={$_GET['uid']}'>".shortname($d['probname'])."</a>";
    echo "<a href='../problem/problem.php?pid={$d['pid']}' target='_blank'><span class='icon-share'></span></a>";
} else
    echo "<a href='../problem/problem.php?pid={$d['pid']}' target='_blank'>".shortname($d['probname'])."</a>";
?></td>
<td><a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'><?=gravatar::showImage($d['email']);?></a>
<?php echo "<a href='?uid={$d[uid]}&pid={$_GET['pid']}'>";
if(有此权限("查看用户")) echo $d['realname']; else echo $d['nickname'];
echo "</a>"; ?></td>
<td class='wrap'><a href='code.php?id=<?=$d['sid']?>'><?=评测结果($d['result'], 20)?></a></td>
<td><span class="<?=$d['accepted']?'ok':'no'?>"><?=$d['score'] ?></span></td>
<td><a href='code.php?id=<?=$d['sid']?>'><?=$STR['lang'][$d['lang']]?></a></td>
<td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
<td><?php printf("%.2f",$d['memory']/1024) ?> MB </td>
<td><?php echo date('Y-m-d H:i:s',$d['subtime']); ?></td>
</tr>
<?php
}
?>
</table>
<?
if($_GET['show'] || $_GET['pid'])
    分页($cnt, $_GET['page'], '?pid='.$_GET['pid'].'&uid='.$_GET['uid'].'&display='.$_GET['display'].'&show='.$_GET['show'].'&');
?>
</div>
<?php
include_once("../include/footer.php");
?>
