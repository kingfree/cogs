<?php
require_once("../include/header.php");
gethead(1,"","记录列表");

$p=new DataAccess();
$q=new DataAccess();
?>
<div class='container'>
<form id="search_submit" action="" method="get" class='center'>
用户UID
<input name="uid" type="text" value="<?=$_GET['uid']?>" />
题目PID
<input name="pid" type="text" value="<?=$_GET['pid']?>" />
<input name="show" type="hidden" value="<?=$_GET['show']?>" />
<input name="display" type="hidden" value="<?=$_GET['display']?>" />
<button type="submit" class='btn'>检索</button>
<p>
<button name='show' value='yes' class='btn'>显示所有记录</button>
这里是 <b><?php
if ((int)$_GET['uid']==0) {
    echo "所有人";
} else {
    $sql="select nickname,email from userinfo where uid='{$_GET['uid']}'";
    $cnt=$p->dosql($sql);
    if (!$cnt) exit;
    $d=$p->rtnrlt(0);
?>
<a href="../user/detail.php?uid=<?php echo $_GET['uid'] ?>" target="_blank"><?=gravatar::showImage($d['email']);?><?php echo $d['nickname'] ?></a><?php } ?></b>的<b><?php
if ((int)$_GET['pid']==0) {
    echo "所有题目";
} else {
    $sql="select probname from problem where pid='{$_GET['pid']}' limit 1";
    $cnt=$p->dosql($sql);
    if (!$cnt) exit;
    $d=$p->rtnrlt(0);
?>
<? 是否通过($_GET['pid'], $q);?><a href="../problem/problem.php?pid=<?php echo $_GET['pid'] ?>" target="_blank"><?php echo $d['probname'] ?></a><?php } ?></b>的记录。
<button name='display' value='all' class='btn'>全部显示</button>
<button name='display' value='ac' class='btn'>只显示通过的</button>
</form>
<?php
$sql="select submit.*,userinfo.nickname,userinfo.email,userinfo.realname,problem.probname from submit,userinfo,problem where submit.pid=problem.pid and submit.uid=userinfo.uid ";
if ($_GET['display']=='ac')
    $sql.=" and submit.accepted=1 ";
if ($_GET['uid'])
    $sql.=" and submit.uid={$_GET['uid']} ";
if ($_GET['pid'])
    $sql.=" and submit.pid={$_GET['pid']} ";
$sql.=" order by submit.sid desc";
if(!$_GET['show']) $sql .= " limit {$SET['style_ranksize']}";
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
<th width='40px'>SID</th>
<th width='120px' onclick="sortTable('submitlist', 1, 'int')">题目</th>
<th width='120px' onclick="sortTable('submitlist', 2, 'int')">用户</th>
<th onclick="sortTable('submitlist', 3)">结果</th>
<th width='40px' onclick="sortTable('submitlist', 4, 'int')">得分</th>
<th width='40px'>语言</th>
<th width='60px' onclick="sortTable('submitlist', 6, 'int')">用时</th>
<th width='80px' onclick="sortTable('submitlist', 7, 'int')">内存</th>
<th width='120px' onclick="sortTable('submitlist', 8)">时间</th>
<?php if(有此权限('查看代码')) { ?>
<th width='50px' class=admin>姓名</th>
<?php } ?>
</tr></thead>
<?php if (!$err)
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=$p->rtnrlt($i);
?>
<tr>
<td><?=$d['sid']?></td>
<td><?php if(!$_GET['pid']) {
    是否通过($d['pid'], $q);
    echo "<a href='?pid={$d['pid']}&uid={$_GET['uid']}'>{$d['probname']}</a>";
    echo "<a href='../problem/problem.php?pid={$d['pid']}' target='_blank'><span class='icon-share'></span></a>";
} else
    echo "<a href='../problem/problem.php?pid={$d['pid']}' target='_blank'>{$d['probname']}</a>";
?></td>
<td><a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'><?=gravatar::showImage($d['email']);?></a><?php echo "<a href='?uid={$d[uid]}&pid={$_GET['pid']}'>{$d['nickname']}</a>"; ?></td>
<td class='wrap'><?=评测结果($d['result'])?></td>
<td><span class="<?=$d['accepted']?'ok':'no'?>"><?=$d['score'] ?></span></td>
<td><a href='code.php?id=<?=$d['sid']?>'><?=$STR['lang'][$d['lang']]?></a></td>
<td><?php printf("%.3f",$d['runtime']/1000.0) ?> s </td>
<td><?php printf("%.2f",$d['memory']/1024) ?> MiB </td>
<td><?php echo date('Y-m-d H:i:s',$d['subtime']); ?></td>
<?php if(有此权限('查看代码')) { ?>
<td><?php echo $d['realname'] ?></td>
<?php } ?>
</tr>
<?php
}
?>
</table>
<?
if($_GET['show'])
    分页($cnt, $_GET['page'], '?pid='.$_GET['pid'].'&uid='.$_GET['uid'].'&display='.$_GET['display'].'&show='.$_GET['show'].'&');
?>
</div>
<?php
include_once("../include/footer.php");
?>
